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
            'address' => $this->address,
            'description'=>$this->description,
            'type'=>$this->type,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'amenities'=>$this->amenities->pluck('name'),
            'rooms'=>RoomResource::collection($this->rooms),
            'reviews'=>ReviewResource::collection($this->approvedReviews),
        ];
    }
}
