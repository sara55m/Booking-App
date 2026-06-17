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
        $nights = $this->nights_count ?? 1;
        $offer = $this->offers->first();
        $price_per_night = $this->rooms()->min('price-per-night');
        $originalPrice = $price_per_night * $nights;
        $finalPrice = $originalPrice;
        $offerBadge = null;

        if($offer){
            $offerService = app(OfferService::class);

            $validation = $offerService->validateOffer(
                userId:      auth()->id(),
                offer:       $offer,
                propertyId:  $this->id,
                totalPrice:  $originalPrice,
                nights:      $nights,
            );

            if($validation['valid']){
                $discount = $offerService->calculateDiscount($offer,$originalPrice);
                $finalPrice = max(0, $originalPrice - $discount);
                $offerBadge = [
                    'badge_label' => $offer->title,
                    'is_expiring' => $offer->ends_at !== null && $offer->ends_at->diffInDays(now()) <= 7,
                ];
            }


        }

        return [
            'id' => $this->id,
            'cover_image' => $this->coverImage ? asset('storage/'.$this->coverImage->image) : null,
            'name' => $this->name,
            'rating' => $this->rating,
            'address' => $this->address,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,

            'original_price' => $offerBadge ? round($originalPrice, 2) . ' EGP' : null,
            'final_price'    => round($finalPrice, 2) . ' EGP',
            'nights'         => $nights,
            'offer'          => $offerBadge,
        ];
    }
}
