<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Offer;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityResource;
use App\Models\Property;
use App\Http\Resources\PropertyResource;
use App\Models\PropertyType;
use App\Http\Resources\PropertyTypeResource;
use App\Http\Resources\OfferResource;
use Illuminate\Support\Str;

class HomeController extends Controller
{

    private function homeCacheKey(string $endpoint): string
    {
        $homeVersion = Cache::rememberForever(
            'cache_versions:home',
            fn () => (string) Str::uuid(),
        );

        $endpointVersion = Cache::rememberForever(
            "cache_versions:home:{$endpoint}",
            fn () => (string) Str::uuid(),
        );

        return "home:{$homeVersion}:{$endpoint}:{$endpointVersion}";
    }

    public function popularCities(){

        $key = $this->homeCacheKey('popular-cities');

        $cities=Cache::remember($key,now()->addHours(6),function(){
            return City::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->withCount([
                'properties' => fn ($query) => $query->where('is_active', true),
            ])
            ->having('properties_count', '>', 0)
            ->with(['country','travelCategories','coverImage'])
            ->orderByDesc('properties_count')
            ->limit(8)
            ->get();
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.cities_retrieved_successfully'),
            'data'=>CityResource::collection($cities)
        ]);
    }

    public function propertyTypes(){

        $key = $this->homeCacheKey('property-types');

        $propertyTypes=Cache::remember($key,now()->addHours(6),function(){
            return PropertyType::query()
            ->where('is_active', true)
            ->withCount([
                'properties' => fn ($query) => $query->where('is_active', true),
            ])
            ->having('properties_count', '>', 0)
            ->orderByDesc('properties_count')
            ->limit(8)
            ->get();
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.property_types_retrieved_successfully'),
            'data'=>PropertyTypeResource::collection($propertyTypes)
        ]);
    }

    public function featuredProperties(){

        $key = $this->homeCacheKey('featured-properties');

        $properties=Cache::remember($key,now()->addHours(6),function(){
            return Property::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->withActiveOffer()
            ->withMin('roomTypes', 'base_price')
            ->with('coverImage','city')
            ->latest()
            ->limit(8)
            ->get();
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.featured_properties_retrieved_successfully'),
            'data'=>PropertyResource::collection($properties)
        ]);
    }

    public function topRatedProperties(){

        $key = $this->homeCacheKey('top-rated-properties');

        $properties=Cache::remember($key,now()->addHours(6),function(){
            return Property::query()
            ->where('is_active', true)
            ->withMin('roomTypes', 'base_price')
            ->withActiveOffer()
            ->with('coverImage','city')
            ->where('reviews_count', '>=', 5)
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->limit(8)
            ->get();
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.top_rated_properties_retrieved_successfully'),
            'data'=>PropertyResource::collection($properties)
        ]);
    }

    public function generalOffers()
    {
        $key = $this->homeCacheKey('general-offers');

        $offers = Cache::remember(
            $key,
            now()->addHours(6),
            function () {
                return Offer::query()
                    ->global()
                    ->active()
                    ->latest('created_at')
                    ->limit(8)
                    ->get();
            }
        );

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.general_offers_retrieved_successfully'),
            'data' => OfferResource::collection($offers),
        ]);
    }

    public function dealsAndOffers(){

        $key = $this->homeCacheKey('deals-and-offers');

        $properties=Cache::remember($key,now()->addHours(6),function(){
            return Property::query()
            ->where('is_active', true)
            ->withMin('roomTypes', 'base_price')
            ->whereHas('offers', function ($query) {
                $query->active();
            })
            ->withActiveOffer()
            ->with(['coverImage','city'])
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count')
            ->limit(8)
            ->get();
        });

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.deals_and_offers_retrieved_successfully'),
            'data'=>PropertyResource::collection($properties)
        ]);
    }

}
