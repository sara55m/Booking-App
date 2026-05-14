<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Http\Requests\Bookings\StoreRequest;
use App\Models\Room;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            ]);
    }

    public function create(StoreRequest $request)
    {
        $data=$request->validated();

        //get room
        $room=Room::findOrFail($data['room_id']);

        //check capacity
        if($data['guests_count'] > $room->capacity) {
            return response()->json(['message' => __('messages.number_of_guests_exceeds_capacity')], 400);
        }
        // Check if room is available for the given dates
        if(!Booking::isRoomAvailable($room->id, $data['check_in'], $data['check_out'])) {
            return response()->json(['message' => __('messages.room_not_available_in_these_dates')], 400);
        }

        $booking=new Booking();
        //calculate number of nights
        $booking->check_in=$data['check_in'];
        $booking->check_out=$data['check_out'];
        //calculate number of nights
        $numberOfNights=$booking->calculateNumberOfNights();
        //calculate total price
        $totalPrice=$booking->calculateTotalPrice($room->{'price-per-night'});

        //save booking
        $booking->user_id=auth()->id();
        $booking->property_id=$data['property_id'];
        $booking->room_id=$data['room_id'];
        $booking->guests_count=$data['guests_count'];
        $booking->nights_count=$numberOfNights;
        $booking->total_price=$totalPrice;
        $booking->status=BookingStatus::PENDING;
        $booking->save();

        return response()->json(
            [
                'status_code' => 201,
                'message' => __('messages.booking_created_successfully'),
                'data' => new BookingResource($booking)]);
    }
}
