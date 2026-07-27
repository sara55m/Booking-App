<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Http\Requests\Bookings\StoreRequest;
use App\Models\Room;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Services\OfferService;
use App\Models\Offer;
use App\Events\BookingCreated;
use App\Services\BookingCancellationService;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'status' => [
                'nullable',
                Rule::in(BookingStatus::values()),
            ],
            'search'=>['nullable','string','max:255']
        ]);
        $bookings=Booking::query()
        ->forUser(auth()->id())
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->status($request->status);
        })
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->whereHas('property', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });
        })
        ->with(['property','room.roomType','offer'])
        ->latest()
        ->paginate(10);

        return response()->json(
            [
                'status_code' => 200,
                'message'=>$bookings->isEmpty()
                ? __('messages.no_bookings_made_yet')
                : __('messages.bookings_retrieved_successfully'),
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
        if($data['guests_count'] > $room->roomType->capacity) {
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
        $originalPrice=$booking->calculateTotalPrice($room->roomType->base_price);

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

            'expires_at'=>now()->addMinutes(15),

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
            'room.roomType',
            'property',
            'offer',
            'user'
        ]);

        //increment offer used count
        if($offer){
            $offer->increment('used_count');
        }

        //fire booking creation event
        event(new BookingCreated($booking));

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

    public function show(Booking $booking)
    {
        Gate::authorize('view',$booking);

        $booking->load(['property','room.roomType','offer']);

        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.booking_retrieved_successfully'),
                'data' => new BookingResource($booking)],200);
    }

    public function cancel(Booking $booking,BookingCancellationService $cancellationService)
    {
        // Check if the booking belongs to the authenticated user
        Gate::authorize('cancel', $booking);

        try{

            $cancellationService->cancel($booking);

            return response()->json(
                [
                    'status_code' => 200,
                    'message' => __('messages.booking_cancelled_successfully'),
                    'data' => new BookingResource($booking)],200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([
                'message' => __('messages.refund_failed'),
            ], 500);

        }
    }

}


