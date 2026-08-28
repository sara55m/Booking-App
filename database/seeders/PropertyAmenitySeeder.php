<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyAmenitySeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::get()->keyBy('name');

        $amenities = Amenity::get()->keyBy('name');

        $propertyAmenities = [

            'Nile Grand Hotel' => [
                'Wi-Fi',
                'Swimming Pool',
                'Restaurant',
                'Parking',
                'Air Conditioning',
                '24-Hour Front Desk',
            ],

            'Cairo City Suites' => [
                'Wi-Fi',
                'Air Conditioning',
                'Parking',
                'Breakfast',
            ],

            'Alexandria Sea View Resort' => [
                'Wi-Fi',
                'Swimming Pool',
                'Restaurant',
                'Parking',
                'Beach Access',
                'Air Conditioning',
            ],

            'Mediterranean Boutique Hotel' => [
                'Wi-Fi',
                'Restaurant',
                'Air Conditioning',
                '24-Hour Front Desk',
            ],

            'Istanbul Grand Hotel' => [
                'Wi-Fi',
                'Swimming Pool',
                'Restaurant',
                'Parking',
                'Spa',
                'Air Conditioning',
                '24-Hour Front Desk',
            ],

            'Bosporus Residence' => [
                'Wi-Fi',
                'Parking',
                'Breakfast',
                'Air Conditioning',
            ],

            'Rome Imperial Hotel' => [
                'Wi-Fi',
                'Restaurant',
                'Parking',
                'Air Conditioning',
                '24-Hour Front Desk',
            ],

            'Roman Holiday Apartments' => [
                'Wi-Fi',
                'Breakfast',
                'Parking',
                'Air Conditioning',
            ],
        ];

        foreach ($propertyAmenities as $propertyName => $amenityNames) {

            $property = $properties[$propertyName] ?? null;

            if (! $property) {
                continue;
            }

            $amenityIds = [];

            foreach ($amenityNames as $amenityName) {

                if (! isset($amenities[$amenityName])) {
                    throw new \Exception(
                        "Amenity '{$amenityName}' was not found."
                    );
                }

                $amenityIds[] = $amenities[$amenityName]->id;
            }

            $property->amenities()->syncWithoutDetaching($amenityIds);
        }
    }
}
