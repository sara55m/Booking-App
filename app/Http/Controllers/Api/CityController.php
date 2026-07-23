<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityDetailsResource;
use App\Http\Resources\PropertyResource;
use App\Http\Requests\Properties\SearchRequest;
use Carbon\Carbon;

class CityController extends Controller
{
    public function show(City $city){

        $city=Cache::tags(['cities'])->remember('cities:{$city->id}:details',now()->addHours(6),function () use ($city) {

            return City::query()
                ->whereKey($city->id)
                ->where('is_active', true)
                ->with([
                    'country',
                    'coverImage',
                    'images',
                    'travelCategories',
                ])
                ->withCount([
                    'properties' => fn ($query) =>
                        $query->where('is_active', true),
                ])
                ->firstOrFail();
            }
        );

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.city_retrieved_successfully'),
            'data' => new CityDetailsResource($city),
        ]);
    }

    public function properties(SearchRequest $request, City $city)
    {
        $validated = $request->validated();

        $nightsCount = isset($validated['check_in'], $validated['check_out'])
            ? Carbon::parse($validated['check_in'])
                ->diffInDays($validated['check_out'])
            : 1;

       $cacheData=[
            'city' => $city->id,
            'search' => $validated['search'] ?? null,
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

        $key = 'cities:properties:' . md5(json_encode($cacheData));

        $properties = Cache::tags(['cities', 'properties'])
            ->remember($key, now()->addMinutes(15), function () use ($validated, $city,$nightsCount) {

                return $city->properties()
                    ->where('is_active', true)
                    ->withMin('roomTypes', 'base_price')
                    ->filter($validated)
                    ->withActiveOffer($nightsCount)
                    ->with('coverImage')
                    ->paginate(10);
            });

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.properties_retrieved_successfully'),
            'data' => PropertyResource::collection($properties)
                ->additional([
                    'nights_count' => $nightsCount,
                ]),
        ]);
    }
}
