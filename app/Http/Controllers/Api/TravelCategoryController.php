<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TravelCategoryResource;
use App\Models\TravelCategory;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityResource;

class TravelCategoryController extends Controller
{
    public function index(){
        $travelCategories=Cache::tags(['travelCategories'])->remember(
            'travel-categories:index',
            now()->addHours(6),
            fn()=>TravelCategory::query()
            ->where('is_active', true)
            ->withCount('cities')
            ->orderBy('sort_order')
            ->get());

            return response()->json([
                'status_code'=>200,
                'message'=>__('messages.travel_categories_retrieved_successfully'),
                'data'=>TravelCategoryResource::collection($travelCategories),
            ]);
    }

    public function cities(TravelCategory $travelCategory)
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
                    ->paginate(12);
            }
        );

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.cities_retrieved_successfully'),
            'data' => CityResource::collection($cities),
        ]);
    }
}
