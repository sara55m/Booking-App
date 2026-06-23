<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityResource;

class HomeController extends Controller
{
    public function popularCities(){

        $cities=Cache::remember('home:popular-cities',now()->addHours(6),function(){
            return City::query()
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
            'message'=>__('messages.cities_retrieved_successfully'),
            'data'=>CityResource::collection($cities)
        ]);
    }
}
