<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyDetailsResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\ReviewResource;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
       //use query scopes for filtering by city and type
       $properties = Property::query()
       ->when($request->city, function ($query) use ($request) {
           $query->city($request->city);
       })
       ->when($request->type, function ($query) use ($request) {
           $query->type($request->type);
       })
       ->with('coverImage')
       ->where('is_active', true)
       ->latest()->paginate(10);

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.properties_retrieved_successfully'),
            'data'=>PropertyResource::collection($properties)
        ]);
    }

    public function show(Property $property)
    {
        $property->load(['coverImage','images','amenities','rooms','approvedReviews.user','approvedReviews.tags']);

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.property_retrieved_successfully'),
            'data'=>new PropertyDetailsResource($property)
        ]);
    }

    public function availability(Request $request,Property $property)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_number' => 'nullable|integer|min:1',
        ]);

        $rooms=$property->rooms()->whereDoesntHave('bookings', function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('check_in', '<', $request->check_out)
                    ->where('check_out', '>', $request->check_in);
            });
        })->when($request->guests_number, function ($query) use ($request) {
            $query->where('capacity', '>=', $request->guests_number);
        })->with('amenities')->get();

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.availability_retrieved_successfully'),
            'data'=>RoomResource::collection($rooms)
        ]);
    }

    public function topReviews(Property $property)
    {
        $reviews = $property->approvedReviews()
        ->with('user','tags')
        ->orderBy('rating', 'desc')
        ->latest()->take(5)->get();

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.top_reviews_retrieved_successfully'),
            'data'=>ReviewResource::collection($reviews)
        ]);
    }


}
