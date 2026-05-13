<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'name' => $this->name,
            'rating' => $this->rating,
            'address' => $this->address,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'starting_price'=>$this->rooms()->min('price-per-night').' EGP',
        ];
    }
}
