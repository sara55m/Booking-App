<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\Bookings\StoreRequest;
use App\Models\Room;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use App\Services\OfferService;
use App\Models\Offer;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'status' => [
                'nullable',
                Rule::in(BookingStatus::values()),
            ]
        ]);
        $bookings=Booking::query()
        ->forUser(auth()->id())
        ->when($request->status,function($query) use ($request){
            $query->status($request->status);
        })
        ->with(['property','room'])
        ->latest()
        ->paginate(10);

        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.bookings_retrieved_successfully'),
                'data' => BookingResource::collection($bookings),
                'pagination' => [

                    'current_page' => $bookings->currentPage(),

                    'last_page' => $bookings->lastPage(),

                    'per_page' => $bookings->perPage(),

                    'total' => $bookings->total(),
                ]
            ],200);
    }

    public function store(StoreRequest $request,OfferService $offerService)
    {
        $data=$request->validated();
        try{
            //use database transactions and locking to prevent double bookings
        return DB::transaction(function () use ($data,$offerService) {
            //get room and lock room to prevent double bookings
        $room=Room::where('id',$data['room_id'])->lockForUpdate()->firstOrFail();

        //check the room belongs to the booking property
        if($data['property_id'] != $room->property_id) {
            return response()->json(['status_code'=>422,'message' => __('messages.room_does_not_belong_to_property')], 422);
        }

        //check capacity
        if($data['guests_count'] > $room->capacity) {
            return response()->json(['status_code'=>422,'message' => __('messages.number_of_guests_exceeds_capacity')], 422);
        }
        // Check if room is available for the given dates
        if(!Booking::isRoomAvailable($room->id, $data['check_in'], $data['check_out'])) {
            return response()->json(['status_code'=>422,'message' => __('messages.room_not_available_in_these_dates')], 422);
        }

        $booking=new Booking();
        //calculate number of nights
        $booking->check_in=$data['check_in'];
        $booking->check_out=$data['check_out'];
        //calculate number of nights
        $numberOfNights=$booking->calculateNumberOfNights();
        //calculate total price
        $originalPrice=$booking->calculateTotalPrice($room->{'price-per-night'});

        $totalPrice=$originalPrice;
        $discountAmount=0;
        $offer=null;
        //validate offer
        if (!empty(trim($data['code'] ?? ''))){
            $offer=Offer::where('code',$data['code'])->lockForUpdate()->first(); //lock offer to prevent double usage
            if (!$offer) {
                return response()->json([
                    'status_code' => 422,
                    'message' => __('messages.invalid_coupon_code'),
                ], 422);
            }
            $validation=$offerService->validateOffer(auth()->id(),$offer,$data['property_id'],$originalPrice,$numberOfNights);
            //if validation is false
            if (!$validation['valid']) {
                return response()->json([
                    'status_code'=>422,
                    'message' => $validation['message']
                ], 422);
            }
            $discountAmount=$offerService->calculateDiscount($offer,$originalPrice);
            //apply the discount amount
            //prevent negative total
            $totalPrice = max(
                0,
                $originalPrice - $discountAmount
            );   
        }
       
        //save booking
        $booking->fill([

            'user_id' => auth()->id(),
        
            'property_id' => $data['property_id'],
        
            'room_id' => $data['room_id'],
        
            'guests_count' => $data['guests_count'],

            'check_in' => $data['check_in'],

            'check_out' => $data['check_out'],
        
            'nights_count' => $numberOfNights,
        
            'status' => BookingStatus::PENDING,
        
            'payment_status' =>
                BookingPaymentStatus::UNPAID,
        
            'offer_id' => $offer?->id,
        
            'original_price' => $originalPrice,
        
            'discount_amount' => $discountAmount,
        
            // IMPORTANT
            'total_price' => $totalPrice,
        ]);
        $booking->save();

        //load relations
        $booking->load([
            'room',
            'property',
            'offer',
        ]);

        //increment offer used count
        $offer->increment('used_count');
        
        return response()->json(
            [
                'status_code' => 201,
                'message' => __('messages.booking_created_successfully'),
                'data' => new BookingResource($booking)],201);
        });
        }catch (\Exception $e) {

            Log::error('Booking creation failed', [

                'error' => $e->getMessage(),

                'trace' => $e->getTraceAsString(),

                'user_id' => auth()->id(),

                'room_id' => $data['room_id'] ?? null,
            ]);

            return response()->json([

                'message' =>
                    __('messages.something_went_wrong'),

            ], 500);
        }

    }

    public function cancel(Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => __('messages.unauthorized_action')], 404);
        }

        // Check if the booking can be cancelled (e.g., only if it's pending or confirmed)
        if (!in_array($booking->status, [BookingStatus::PENDING, BookingStatus::CONFIRMED])) {
            return response()->json(['message' => __('messages.booking_cannot_be_cancelled')], 422);
        }

        //check the booking did not start yet
        if($booking->check_in->isPast()){
            return response()->json(['message' => __('messages.booking_cannot_be_cancelled_as_it_has_started')], 422);
        }

        //handle refund
        Stripe::setApiKey(config('services.stripe.secret'));
        
        DB::beginTransaction();

        try{
            //get all paid payments for the booking
            $payments=$booking->payments()->where('status',PaymentStatus::PAID)->get();

            //refund each payment amount using stripe refund
            foreach($payments as $payment)
            {
                \Stripe\Refund::create([
                    'payment_intent' =>
                    $payment->stripe_payment_intent_id,
                ]);

                //update payment status and refund data
                $payment->update([
                    'status'=>PaymentStatus::REFUNDED,
                    'refunded_amount' =>$payment->amount,
                    'refunded_at' => now(),
                ]);
            }

            // Update the booking status to cancelled and booking payment status to refunded
            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'payment_status'=>BookingPaymentStatus::REFUNDED,
            ]);

            DB::commit();
            return response()->json(
                [
                    'status_code' => 200,
                    'message' => __('messages.booking_cancelled_successfully'),
                    'data' => new BookingResource($booking)],200);
        }catch (\Exception $e) {

            DB::rollBack();
    
            Log::error('Refund failed', [
    
                'booking_id' => $booking->id,
    
                'error' => $e->getMessage(),
            ]);
    
            return response()->json([
    
                'message' =>
                    __('messages.refund_failed'),
    
            ], 500);
        }
    }
    
}


