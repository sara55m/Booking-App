<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityResource;
use App\Models\Property;
use App\Http\Resources\PropertyResource;
use App\Models\PropertyType;
use App\Http\Resources\PropertyTypeResource;
use App\Http\Resources\TravelCategoryResource;
use App\Models\TravelCategory;

class HomeController extends Controller
{
    public function popularCities(){

        $cities=Cache::tags(['home'])->remember('home:popular-cities',now()->addHours(6),function(){
            return City::query()
            ->where('is_active', true)
            ->withCount([
                'properties' => fn ($query) => $query->where('is_active', true),
            ])
            ->having('properties_count', '>', 0)
            ->with('country','travelCategories')
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

        $propertyTypes=Cache::tags(['home'])->remember('home:property-types',now()->addHours(6),function(){
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

        $properties=Cache::tags(['home'])->remember('home:featured-properties',now()->addHours(6),function(){
            return Property::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->withActiveOffer()
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
        $properties=Cache::tags(['home'])->remember('home:top-rated-properties',now()->addHours(6),function(){
            return Property::query()
            ->where('is_active', true)
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

    public function dealsAndOffers(){

        $properties=Cache::tags(['home'])->remember('home:deals-and-offers',now()->addHours(6),function(){
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

    public function travelCategories(){
        $travelCategories=Cache::tags(['travelCategories'])->remember(
            'travel-categories:index',
            now()->addHours(6),
            fn()=>TravelCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());

            return response()->json([
                'status_code'=>200,
                'message'=>__('messages.travel_categories_retrieved_successfully'),
                'data'=>TravelCategoryResource::collection($travelCategories),
            ]);
    }

    public function travelCategoryCities(TravelCategory $travelCategory)
    {
        $cacheKey ="travel-categories:{$travelCategory->id}:cities";

        $cities = Cache::tags(['travelCategories'])->remember(
            $cacheKey,
            now()->addHours(6),
            function () use ($travelCategory) {
                return $travelCategory
                    ->cities()
                    ->where('is_active', true)
                    ->with([
                        'country',
                        'coverImage',
                    ])
                    ->withCount([
                        'properties' => fn ($query) => $query->where('is_active', true),
                    ])
                    ->orderByDesc('properties_count')
                    ->get();
            }
        );

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.cities_retrieved_successfully'),
            'data' => CityResource::collection($cities),
        ]);
    }


}
