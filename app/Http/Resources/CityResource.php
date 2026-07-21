<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class CityResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'country' =>[
                'id' => $this->country->id,
                'name' => $this->country->name,
            ],
            'description'=>$this->description,
            'latitude'=>$this->latitude,
            'longitude'=>$this->longitude,
            'cover_image'=> $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'images'=>CityImageResource::collection($this->images),
            'travel_categories' =>
                TravelCategoryResource::collection(
                    $this->whenLoaded('travelCategories')
                ),
        ];
    }
}
