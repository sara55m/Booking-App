<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'property_id'=>$this->property_id,
            'name'=>$this->name,
            'number'=>$this->number,
            'description'=>$this->description ?? null,
            'amenities'=>$this->amenities->map(function($amenity){
                return [
                    'id'=>$amenity->id,
                    'name'=>$amenity->name,
                    'icon'=>$amenity->icon ? asset('storage/'.$amenity->icon) : null,
                ];
            }),
            'price_per_night'=>$this->{'price-per-night'}.' EGP',
            'capacity'=>$this->capacity,
        ];
    }
}
