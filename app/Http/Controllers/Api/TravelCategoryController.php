<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TravelCategoryResource;
use App\Models\TravelCategory;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityResource;
use Illuminate\Support\Str;

class TravelCategoryController extends Controller
{
    private function cacheKey(string $endpoint): string
    {
        $globalVersion = Cache::rememberForever(
            'cache_versions:travel-categories',
            fn () => (string) Str::uuid(),
        );

        $endpointVersion = Cache::rememberForever(
            "cache_versions:travel-categories:{$endpoint}",
            fn () => (string) Str::uuid(),
        );

        return "travel-categories:{$globalVersion}:{$endpoint}:{$endpointVersion}";
    }

    public function index(){

        // index()
        $cacheKey = $this->cacheKey('index');

        $travelCategories=Cache::remember(
            $cacheKey,
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
        $propertyVersion = Cache::rememberForever(
            'cache_versions:properties',
            fn () => (string) Str::uuid(),
        );

        $page = request()->query('page', 1);

        $cacheKey = $this->cacheKey("cities:{$travelCategory->id}")
            . ":properties:{$propertyVersion}:page:{$page}";

        $cities = Cache::remember(
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
