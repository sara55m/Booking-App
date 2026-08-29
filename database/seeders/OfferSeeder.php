<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties=Property::get()->keyBy('name');

        $now = now();

        $offers = [

            /*
            |--------------------------------------------------------------------------
            | Global Offers
            |--------------------------------------------------------------------------
            */

            [
                'property' => null,
                'title' => 'Summer Escape',
                'code' => 'SUMMER15',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'minimum_booking_amount' => null,
                'minimum_nights' => null,
                'is_active' => true,
                'notify_users' => false,
                'starts_at' => $now->copy()->startOfDay(),
                'ends_at' => $now->copy()->addDays(30)->endOfDay(),
                'usage_limit' => 500,
                'per_user_limit' => 1,
                'requires_coupon_code' => true,
            ],

            [
                'property' => null,
                'title' => 'Extended Stay Deal',
                'code' => 'STAY3SAVE',
                'discount_type' => 'percentage',
                'discount_value' => 12,
                'minimum_booking_amount' => null,
                'minimum_nights' => 3,
                'is_active' => true,
                'notify_users' => false,
                'starts_at' => $now->copy()->addDays(2)->startOfDay(),
                'ends_at' => $now->copy()->addDays(45)->endOfDay(),
                'usage_limit' => 300,
                'per_user_limit' => 2,
                'requires_coupon_code' => true,
            ],

            [
                'property' => null,
                'title' => 'Early Bird Offer',
                'code' => 'EARLY10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_booking_amount' => null,
                'minimum_nights' => 2,
                'is_active' => true,
                'notify_users' => false,
                'starts_at' => $now->copy()->addDays(7)->startOfDay(),
                'ends_at' => $now->copy()->addDays(90)->endOfDay(),
                'usage_limit' => 200,
                'per_user_limit' => 1,
                'requires_coupon_code' => true,
            ],

            [
                'property' => null,
                'title' => 'Last Minute Deal',
                'code' => 'LASTMINUTE20',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'minimum_booking_amount' => null,
                'minimum_nights' => 1,
                'is_active' => true,
                'notify_users' => false,
                'starts_at' => $now->copy()->addDays(1)->startOfDay(),
                'ends_at' => $now->copy()->addDays(14)->endOfDay(),
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'requires_coupon_code' => true,
            ],

            [
                'property' => null,
                'title' => 'Weekend Getaway',
                'code' => 'WEEKEND500',
                'discount_type' => 'fixed',
                'discount_value' => 500,
                'minimum_booking_amount' => 5000,
                'minimum_nights' => 2,
                'is_active' => true,
                'notify_users' => false,
                'starts_at' => $now->copy()->addDays(15)->startOfDay(),
                'ends_at' => $now->copy()->addDays(45)->endOfDay(),
                'usage_limit' => 150,
                'per_user_limit' => 1,
                'requires_coupon_code' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Property Specific Offers
            |--------------------------------------------------------------------------
            */

            [
                'property' => 'Cairo City Suites',
                'title' => 'Cairo City Special',
                'code' => null,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'minimum_booking_amount' => 3000,
                'minimum_nights' => 2,
                'is_active' => true,
                'notify_users' => true,
                'starts_at' => $now->copy()->startOfDay(),
                'ends_at' => $now->copy()->addDays(60)->endOfDay(),
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'requires_coupon_code' => false,
            ],

            [
                'property' => 'Istanbul Grand Hotel',
                'title' => 'Istanbul Luxury Escape',
                'code' => null,
                'discount_type' => 'percentage',
                'discount_value' => 18,
                'minimum_booking_amount' => 7000,
                'minimum_nights' => 2,
                'is_active' => true,
                'notify_users' => true,
                'starts_at' => $now->copy()->addDays(5)->startOfDay(),
                'ends_at' => $now->copy()->addDays(50)->endOfDay(),
                'usage_limit' => 75,
                'per_user_limit' => 1,
                'requires_coupon_code' => false,
            ],

            [
                'property' => 'Rome Imperial Hotel',
                'title' => 'Rome Stay & Save',
                'code' => null,
                'discount_type' => 'fixed',
                'discount_value' => 600,
                'minimum_booking_amount' => 6000,
                'minimum_nights' => 3,
                'is_active' => true,
                'notify_users' => true,
                'starts_at' => $now->copy()->addDays(10)->startOfDay(),
                'ends_at' => $now->copy()->addDays(70)->endOfDay(),
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'requires_coupon_code' => false,
            ],
        ];

        foreach($offers as $offerData){
            $propertyId=null;//global offers
            if($offerData['property']){
                $property = $properties[$offerData['property']] ?? null;

                if(! $property){
                    throw new \Exception(
                        "Property '{$offerData['property']}' was not found."
                    );
                }
                $propertyId=$property->id;
            }

            Offer::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'title' => $offerData['title'],
                ],
                [
                    'code'=>$offerData['code'],
                    'discount_type' => $offerData['discount_type'],
                    'discount_value' => $offerData['discount_value'],
                    'minimum_booking_amount' => $offerData['minimum_booking_amount'],
                    'minimum_nights' => $offerData['minimum_nights'],
                    'is_active' => $offerData['is_active'],
                    'notify_users' => $offerData['notify_users'],
                    'notification_sent_at' => null,
                    'starts_at' => $offerData['starts_at'],
                    'ends_at' => $offerData['ends_at'],
                    'usage_limit' => $offerData['usage_limit'],
                    'per_user_limit' => $offerData['per_user_limit'],
                    'requires_coupon_code' => $offerData['requires_coupon_code'],
                ]
            );

        }
    }
}
