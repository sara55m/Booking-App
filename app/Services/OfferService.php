<?php

namespace App\Services;

use App\Models\Offer;

class OfferService
{

    public function validateOffer(Offer $offer,$propertyId,$totalPrice,$nights) : array{

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
}