<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyDetailsResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\ReviewResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Rooms\CheckAvailabilityRequest;
use App\Http\Requests\Properties\SearchRequest;
class PropertyController extends Controller
{
    public function index(SearchRequest $request)
    {
        $validated = $request->validated();

        $nightsCount =
        isset($validated['check_in'], $validated['check_out'])
            ? Carbon::parse($validated['check_in'])
                ->diffInDays($validated['check_out'])
            : 1;

        //make cache key based on request parameters
        $cacheData = [
            'search' => $validated['search'] ?? null,
            'city' => $validated['city'] ?? null,
            'type' => $validated['type'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'min_price'=>$validated['min_price'] ?? null,
            'max_price'=>$validated['max_price'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'property_amenities'=>$validated['property_amenities'] ?? null,
            'room_amenities'=>$validated['room_amenities'] ?? null,
            'guests' => $validated['guests'] ?? null,
            'check_in' => $validated['check_in'] ?? null,
            'check_out' => $validated['check_out'] ?? null,
            'page' => $validated['page'] ?? 1,
        ];
        $key = 'properties:' . md5(json_encode($cacheData));

        $properties=Cache::tags(['properties'])
        ->remember($key, now()->addMinutes(15), function () use ($validated,$nightsCount) {
            return Property::query()
                ->where('is_active', true)
                ->withMin('roomTypes', 'base_price')
                ->filter($validated)
                ->withActiveOffer($nightsCount)
                ->with(['coverImage','city.country'])
                ->paginate(10);
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.properties_retrieved_successfully'),
            'data'=>PropertyResource::collection($properties)->additional(['nights_count'=>$nightsCount])
        ]);
    }

    public function show(Property $property)
    {
        //cache the property details for 30 minutes to reduce database queries
        $property=Cache::remember("property:{$property->id}",now()->addMinutes(30),function() use ($property){
            return Property::with(['policy','coverImage','images','amenities','rooms.roomType','approvedReviews.user','approvedReviews.tags','city','propertyType'])->findOrFail($property->id);
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.property_retrieved_successfully'),
            'data'=>new PropertyDetailsResource($property)
        ]);
    }

    public function availability(CheckAvailabilityRequest $request,Property $property)
    {
        $validated = $request->validated();

        $rooms=$property->rooms()->availableBetween($validated['check_in'], $validated['check_out'])
        ->forGuests($validated['guests_number'] ?? null)
        ->forType($validated['room_type_id'] ?? null)
        ->with([
            'roomType.amenities',
            'images',
            'coverImage',
        ])
        ->paginate(10);;

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.availability_retrieved_successfully'),
            'data'=>RoomResource::collection($rooms),
            'pagination' => [

                    'current_page' => $rooms->currentPage(),

                    'last_page' => $rooms->lastPage(),

                    'per_page' => $rooms->perPage(),

                    'total' => $rooms->total(),
                ]
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

    public function addToFavorites(Property $property){

        $user = auth()->user();

        if($user->favoriteProperties()->whereKey($property->id)->exists()){
            return response()->json([
                'status_code'=>409,
                'message'=>__('messages.property_already_in_favorites'),
            ],409);
        }

        $user->favoriteProperties()->attach([$property->id]);

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.property_added_to_favorites_successfully'),
        ]);

    }

    public function removeFromFavorites(Property $property){

        $user = auth()->user();

        if(! $user->favoriteProperties()->whereKey($property->id)->exists() ){
            return response()->json([
                'status_code'=>404,
                'message'=>__('messages.property_not_in_favorites'),
            ],404);
        }

        $user->favoriteProperties()->detach($property->id);

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.property_removed_from_favorites_successfully'),
        ]);

    }


}
