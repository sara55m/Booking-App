<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::get()->keyBy('name');

        $roomTypes = [

            'Nile Grand Hotel' => [
                [
                    'name' => 'Deluxe Nile View Room',
                    'description' => 'Spacious room with a beautiful view of the Nile River.',
                    'capacity' => 2,
                    'base_price' => 3500,
                ],
                [
                    'name' => 'Executive Suite',
                    'description' => 'Luxury suite with a separate living area and premium amenities.',
                    'capacity' => 3,
                    'base_price' => 5500,
                ],
            ],

            'Cairo City Suites' => [
                [
                    'name' => 'Standard Suite',
                    'description' => 'Comfortable modern suite suitable for couples.',
                    'capacity' => 2,
                    'base_price' => 2200,
                ],
                [
                    'name' => 'Family Suite',
                    'description' => 'Spacious suite designed for families.',
                    'capacity' => 4,
                    'base_price' => 3200,
                ],
            ],

            'Alexandria Sea View Resort' => [
                [
                    'name' => 'Sea View Room',
                    'description' => 'Comfortable room with a direct Mediterranean Sea view.',
                    'capacity' => 2,
                    'base_price' => 3000,
                ],
                [
                    'name' => 'Family Sea View Suite',
                    'description' => 'Large family suite overlooking the Mediterranean Sea.',
                    'capacity' => 5,
                    'base_price' => 4800,
                ],
            ],

            'Mediterranean Boutique Hotel' => [
                [
                    'name' => 'Boutique Double Room',
                    'description' => 'Stylish and comfortable room with a modern design.',
                    'capacity' => 2,
                    'base_price' => 2500,
                ],
                [
                    'name' => 'Premium Suite',
                    'description' => 'Elegant suite with additional space and premium comfort.',
                    'capacity' => 3,
                    'base_price' => 4000,
                ],
            ],

            'Istanbul Grand Hotel' => [
                [
                    'name' => 'Deluxe City View Room',
                    'description' => 'Luxury room with views of Istanbul city.',
                    'capacity' => 2,
                    'base_price' => 4000,
                ],
                [
                    'name' => 'Sultan Suite',
                    'description' => 'Premium luxury suite inspired by Ottoman elegance.',
                    'capacity' => 4,
                    'base_price' => 7000,
                ],
            ],

            'Bosporus Residence' => [
                [
                    'name' => 'Bosporus View Apartment',
                    'description' => 'Modern apartment with a beautiful Bosporus view.',
                    'capacity' => 3,
                    'base_price' => 4500,
                ],
                [
                    'name' => 'Family Residence',
                    'description' => 'Spacious apartment suitable for families.',
                    'capacity' => 5,
                    'base_price' => 6000,
                ],
            ],

            'Rome Imperial Hotel' => [
                [
                    'name' => 'Classic Double Room',
                    'description' => 'Elegant room located in the heart of Rome.',
                    'capacity' => 2,
                    'base_price' => 3800,
                ],
                [
                    'name' => 'Imperial Suite',
                    'description' => 'Luxury suite with classic Roman-inspired design.',
                    'capacity' => 4,
                    'base_price' => 6500,
                ],
            ],

            'Roman Holiday Apartments' => [
                [
                    'name' => 'One Bedroom Apartment',
                    'description' => 'Comfortable apartment ideal for couples or small families.',
                    'capacity' => 3,
                    'base_price' => 3000,
                ],
                [
                    'name' => 'Two Bedroom Apartment',
                    'description' => 'Spacious apartment with two bedrooms for families.',
                    'capacity' => 5,
                    'base_price' => 5000,
                ],
            ],
        ];

        foreach ($roomTypes as $propertyName => $types) {

            $property = $properties[$propertyName] ?? null;

            if (! $property) {
                continue;
            }

            foreach ($types as $roomType) {

                $property->roomTypes()->updateOrCreate(
                    [
                        'name' => $roomType['name'],
                    ],
                    [
                        'description' => $roomType['description'],
                        'capacity' => $roomType['capacity'],
                        'base_price' => $roomType['base_price'],
                    ]
                );
            }
        }
    }
}
