<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyPolicySeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::get()->keyBy('name');

        $policies = [
            'Nile Grand Hotel'=>[
                'check_in_from' => '14:00',
                'check_in_until' => '23:00',
                'check_out_from' => '07:00',
                'check_out_until' => '12:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'A valid ID is required at check-in.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 24,
                'refund_percentage' => 50,
            ],

            'Cairo City Suites' => [
                'check_in_from' => '14:00',
                'check_in_until' => '23:00',
                'check_out_from' => '07:00',
                'check_out_until' => '12:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'A valid ID is required at check-in.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 24,
                'refund_percentage' => 100,
            ],

            'Alexandria Sea View Resort' => [
                'check_in_from' => '15:00',
                'check_in_until' => '23:00',
                'check_out_from' => '07:00',
                'check_out_until' => '11:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'Guests must present a valid ID upon arrival.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 48,
                'refund_percentage' => 100,
            ],

            'Mediterranean Boutique Hotel' => [
                'check_in_from' => '14:00',
                'check_in_until' => '22:00',
                'check_out_from' => '08:00',
                'check_out_until' => '12:00',
                'pets_allowed' => true,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'Please contact the property if you expect to arrive after 22:00.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 24,
                'refund_percentage' => 100,
            ],

            'Istanbul Grand Hotel' => [
                'check_in_from' => '14:00',
                'check_in_until' => '00:00',
                'check_out_from' => '07:00',
                'check_out_until' => '12:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'A passport or valid ID is required at check-in.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 48,
                'refund_percentage' => 100,
            ],

            'Bosporus Residence' => [
                'check_in_from' => '15:00',
                'check_in_until' => '23:00',
                'check_out_from' => '08:00',
                'check_out_until' => '11:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'Guests are required to show a valid ID.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 24,
                'refund_percentage' => 100,
            ],

            'Rome Imperial Hotel' => [
                'check_in_from' => '14:00',
                'check_in_until' => '23:00',
                'check_out_from' => '07:00',
                'check_out_until' => '12:00',
                'pets_allowed' => false,
                'children_allowed' => true,
                'smoking_allowed' => false,
                'minimum_check_in_age' => 18,
                'important_information' => 'Guests must present a valid identification document.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 48,
                'refund_percentage' => 100,
            ],

            'Roman Holiday Apartments'=> [
                'check_in_from' => '12:00',
                'check_in_until' => '21:00',
                'check_out_from' => '08:00',
                'check_out_until' => '14:00',
                'pets_allowed' => true,
                'children_allowed' => true,
                'smoking_allowed' => true,
                'minimum_check_in_age' => 16,
                'important_information' => 'Guests must present a valid identification document.',
                'free_cancellation' => true,
                'free_cancellation_hours' => 48,
                'refund_percentage' => 100,
            ],
        ];

        foreach ($policies as $propertyName => $policyData) {
            $property = $properties[$propertyName] ?? null;

            if (! $property) {
                throw new \Exception(
                    "Property '{$propertyName}' was not found."
                );
            }

            $property->policy()->updateOrCreate(
                [],
                $policyData
            );
        }
    }
}
