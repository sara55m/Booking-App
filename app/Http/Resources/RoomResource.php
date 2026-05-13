<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RoomImageResource;

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
            'cover_image' => $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'images'=>RoomImageResource::collection($this->images),
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
