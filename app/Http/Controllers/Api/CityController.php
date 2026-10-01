<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityDetailsResource;
use App\Http\Resources\PropertyResource;
use App\Http\Requests\Properties\SearchRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function show(City $city){
        $citiesVersion = Cache::rememberForever(
            'cache_versions:cities',
            fn () => (string) Str::uuid(),
        );

        $cityVersion = Cache::rememberForever(
            "cache_versions:cities:{$city->id}",
            fn () => (string) Str::uuid(),
        );

        $key = "cities:{$citiesVersion}:{$city->id}:details:{$cityVersion}";

        $city=Cache::remember($key,now()->addHours(6),function () use ($city) {

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

        $nightsCount = 1;

        if (!empty($validated['check_in']) && !empty($validated['check_out'])) {
            $nightsCount = Carbon::parse($validated['check_in'])
                ->diffInDays(Carbon::parse($validated['check_out']));
        }

       $cacheData=[
            'country' => $city->country->id,
            'city' => $city->id,
            'search' => $validated['search'] ?? null,
            'type' => $validated['type'] ?? null,
            'guest_rating' => $validated['guest_rating'] ?? null,
            'hotel_rating' => $validated['hotel_rating'] ?? null,
            'min_price'=>$validated['min_price'] ?? null,
            'max_price'=>$validated['max_price'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'property_amenities'=>$validated['property_amenities'] ?? null,
            'room_amenities'=>$validated['room_amenities'] ?? null,
            'guests' => $validated['guests'] ?? null,
            'check_in' => $validated['check_in'] ?? null,
            'check_out' => $validated['check_out'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'radius' => $validated['radius'] ?? null,
            'page' => $validated['page'] ?? 1,
        ];

        $citiesVersion = Cache::rememberForever(
            'cache_versions:cities',
            fn () => (string) Str::uuid(),
        );

        $cityVersion = Cache::rememberForever(
            "cache_versions:cities:properties:{$city->id}",
            fn () => (string) Str::uuid(),
        );

        $propertiesVersion = Cache::rememberForever(
            'cache_versions:properties',
            fn () => (string) Str::uuid(),
        );

        $key = 'cities:properties:'
            . $citiesVersion . ':'
            . $city->id . ':'
            . $cityVersion . ':'
            . $propertiesVersion . ':'
            . md5(json_encode($cacheData));

        $properties = Cache::remember($key, now()->addMinutes(15), function () use ($validated, $city,$nightsCount) {

                return $city->properties()
                    ->where('is_active', true)
                    ->withMin('roomTypes', 'base_price')
                    ->filter($validated)
                    ->withActiveOffer($nightsCount)
                    ->with('coverImage')
                    ->paginate(10);
            });

        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.properties_retrieved_successfully'),
            'data' => PropertyResource::collection($properties)
        ]);
    }
}
