<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PropertyImageResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\ReviewResource;

class PropertyDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cover_image' => $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'images'=>PropertyImageResource::collection($this->images),
            'name' => $this->name,
            'rating' => $this->rating,
            'city' => $this->city?->name,
            'address' => $this->address,
            'description'=>$this->description,
            'type'=>$this->propertyType?->name,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'amenities'=>$this->amenities->map(function($amenity){
                return [
                    'id'=>$amenity->id,
                    'name'=>$amenity->name,
                    'icon'=>$amenity->icon ? asset('storage/'.$amenity->icon) : null,
                ];
            }),
            'policy' => PropertyPolicyResource::make(
                    $this->whenLoaded('policy')
                ),
            'minimum_partial_payment_percentage'=>$this->minimum_partial_payment_percentage ?? config('booking.minimum_partial_payment_percentage'),
            'rooms'=>RoomResource::collection($this->rooms),
            'reviews'=>ReviewResource::collection($this->approvedReviews),
        ];
    }
}
