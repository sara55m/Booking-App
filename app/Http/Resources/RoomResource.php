<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RoomImageResource;
use App\Services\CurrencyService;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currency = strtoupper(
            auth()->user()?->currency ?? config('app.currency', 'USD')
        );

        $baseCurrency = strtoupper(config('app.currency', 'USD'));

        $currencyService = app(CurrencyService::class);

        $pricePerNight = $currencyService->convert(
            $this->roomType->base_price,
            $baseCurrency,
            $currency
        );
        return [
            'id'=>$this->id,
            'room_number'=>$this->number,
            'property' => [
                'id' => $this->property->id,
                'name' => $this->property->name,
            ],

            'type' => [
                'id' => $this->roomType->id,
                'name' => $this->roomType->name,
                'description' => $this->roomType->description,
                'capacity' => $this->roomType->capacity,
                'price_per_night' => number_format($pricePerNight, 2)
                . ' ' . $currency,
            ],
            'cover_image' => $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'images'=>RoomImageResource::collection($this->images),
            'description'=>$this->description ?? null,
            'amenities'=>$this->roomType->amenities->map(function($amenity){
                return [
                    'id'=>$amenity->id,
                    'name'=>$amenity->name,
                    'icon'=>$amenity->icon,
                ];
            }),
        ];
    }
}
