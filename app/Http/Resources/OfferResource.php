<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CurrencyService;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //get user preferred currency
        $currency = strtoupper(
            auth()->user()?->currency ?? config('app.currency', 'USD')
        );

        $discountValue = $this->discount_type === 'percentage'
        ? $this->discount_value
        : app(currencyService::class)->convert($this->discount_value, config('app.currency'), $currency);

        $minimumBookingAmount = $this->minimum_booking_amount === null
            ? null
            : app(currencyService::class)->convert($this->minimum_booking_amount, config('app.currency'), $currency);

        return [
            'id' => $this->id,
            'title' => $this->title,

            'code' => $this->code,

            'discount_type' =>$this->discount_type,
            'discount_value' =>$discountValue,

            'formatted_discount' => $this->discount_type === 'percentage'
                ? number_format((float) $discountValue, 2) . ' %'
                : number_format((float) $discountValue, 2) . ' ' . $currency,

            'minimum_booking_amount' => $minimumBookingAmount === null
                ? null
                : number_format((float) $minimumBookingAmount, 2) . ' ' . $currency,

            'minimum_nights' => $this->minimum_nights,

            'starts_at' => $this->starts_at->format('Y-m-d H:i:s'),
            'ends_at' => $this->ends_at->format('Y-m-d H:i:s'),

            'requires_coupon_code' => $this->requires_coupon_code,
        ];
    }
}
