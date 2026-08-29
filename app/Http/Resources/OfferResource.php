<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'title' => $this->title,

            'code' => $this->code,

            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'formatted_discount' => $this->formatted_discount,

            'minimum_booking_amount' => $this->minimum_booking_amount,
            'minimum_nights' => $this->minimum_nights,

            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,

            'requires_coupon_code' => $this->requires_coupon_code,
        ];
    }
}
