<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\OfferService;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pricing=app(OfferService::class)->calculatePrice($this->resource, $request->nights ?? 1);

        return [
            'id' => $this->id,
            'cover_image' => $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'name' => $this->name,
            'rating' => $this->rating,
            'address' => $this->address,
            'average_rating' => $this->average_rating,
            'city' => $this->city?->name,
            'reviews_count' => $this->reviews_count,

            'original_price' => $pricing['originalPrice'] ? round($pricing['originalPrice'], 2): null,
            'final_price'    => $pricing['finalPrice'] ? round($pricing['finalPrice'], 2) : null,
            'currency' => 'EGP',
            'nights'         => $request->nights ?? 1,
            'offer'          => $pricing['offer'] ? OfferResource::make($pricing['offer']) : null,
            'is_favorite' => auth()->check()
            ? auth()->user()
                ->favoriteProperties
                ->contains($this->id)
            : false,
        ];
    }
}
