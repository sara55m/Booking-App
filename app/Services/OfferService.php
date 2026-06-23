<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Property;


class OfferService
{

    public function validateOffer($userId,Offer $offer,$propertyId,$totalPrice,$nights) : array{

        //check usage limit
        if($offer->usage_limit && $offer->used_count >= $offer->usage_limit){
            return [
                'valid' => false,
                'message' =>
                    __('messages.offer_usage_limit_reached'),
            ];
        }

        //check per user limit
        //get the number of bookings with this offer and the same user
        $userUsageCount=$offer->bookings()->where('user_id',$userId)->count();
        if($offer->per_user_limit && $userUsageCount >= $offer->per_user_limit){
            return [
                'valid' => false,
                'message' =>
                    __('messages.user_offer_limit_reached'),
            ];
        }

        //check property
        if($offer->property_id && $offer->property_id!=$propertyId){
            return [
                'valid' => false,
                'message' =>
                    __('messages.offer_not_valid_for_this_property'),
            ];
        }
        //check the offer is active
        if(!$offer->is_active){
            return
            [
                'valid'=>false,
                'message'=> __('messages.offer_is_not_active'),
            ];
        }

        //check the start and end dates
        if($offer->starts_at && $offer->starts_at->isFuture()){
            return [
                'valid' => false,
                'message' => __('messages.offer_not_started_yet'),
            ];
        }

        if($offer->ends_at && $offer->ends_at->isPast()){
            return [
                'valid' => false,
                'message' => __('messages.offer_has_expired'),
            ];
        }

        //check minimum booking amount
        if($offer->minimum_booking_amount && $totalPrice < $offer->minimum_booking_amount){
            return [
                'valid' => false,
                'message' =>
                    __('messages.booking_amount_does_not_meet_offer_requirement'),
            ];
        }

        //check minimum nights
        if($offer->minimum_nights && $nights < $offer->minimum_nights){
            return [
                'valid' => false,
                'message' =>
                    __('messages.minimum_nights_not_met'),
            ];
        }

        //when offer is valid
        return [
            'valid' => true,
            'message' => __('messages.offer_is_valid'),
        ];
    }

    public function calculateDiscount(Offer $offer,$originalPrice) :float{
        //check offer discount type
        if($offer->discount_type === "percentage")
        {
            return $originalPrice*$offer->discount_value/100;
        }

        if($offer->discount_type === "fixed")
        {
            return min($originalPrice,$offer->discount_value);
        }

        return 0;
    }

    public function calculatePrice(Property $property,int $nights=1) : array{

        $offer = $property->offers->first();

        $pricePerNight = $property->rooms_min_pricepernight;

        $originalPrice = $pricePerNight * $nights;

        if(!$offer){
            return [
                'originalPrice' => $originalPrice,
                'finalPrice' => $originalPrice,
                'offer' => null
            ];
        }

            $validation = $this->validateOffer(
                userId:      auth()->id(),
                offer:       $offer,
                propertyId:  $property->id,
                totalPrice:  $originalPrice,
                nights:      $nights,
            );

            if(! $validation['valid']){
                return [
                    'originalPrice' => $originalPrice,
                    'finalPrice' => $originalPrice,
                    'offer' => null
                ];
            }

            $discount = $this->calculateDiscount($offer,$originalPrice);
            $finalPrice = max(0, $originalPrice - $discount);

            return [
                'originalPrice' => $originalPrice,
                'finalPrice' => $finalPrice,
                'discount' => $discount,
                'offer' => $offer,
            ];

    }

}
