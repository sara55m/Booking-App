<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::with('roomTypes')
            ->get()
            ->keyBy('name');

        $rooms = [

            'Nile Grand Hotel' => [
                [
                    'room_type' => 'Deluxe Nile View Room',
                    'number' => '101',
                    'description' => 'Deluxe room with a beautiful Nile River view.',
                ],
                [
                    'room_type' => 'Deluxe Nile View Room',
                    'number' => '102',
                    'description' => 'Comfortable deluxe room overlooking the Nile.',
                ],
                [
                    'room_type' => 'Deluxe Nile View Room',
                    'number' => '103',
                    'description' => 'Spacious room with modern amenities and a Nile view.',
                ],
                [
                    'room_type' => 'Executive Suite',
                    'number' => '201',
                    'description' => 'Luxury executive suite with a separate living area.',
                ],
                [
                    'room_type' => 'Executive Suite',
                    'number' => '202',
                    'description' => 'Premium suite with spacious living and sleeping areas.',
                ],
            ],

            'Cairo City Suites' => [
                [
                    'room_type' => 'Standard Suite',
                    'number' => '101',
                    'description' => 'Modern and comfortable suite for couples.',
                ],
                [
                    'room_type' => 'Standard Suite',
                    'number' => '102',
                    'description' => 'Well-equipped suite in downtown Cairo.',
                ],
                [
                    'room_type' => 'Family Suite',
                    'number' => '201',
                    'description' => 'Spacious suite designed for a comfortable family stay.',
                ],
                [
                    'room_type' => 'Family Suite',
                    'number' => '202',
                    'description' => 'Large family suite with additional living space.',
                ],
            ],

            'Alexandria Sea View Resort' => [
                [
                    'room_type' => 'Sea View Room',
                    'number' => '101',
                    'description' => 'Comfortable room with a direct Mediterranean Sea view.',
                ],
                [
                    'room_type' => 'Sea View Room',
                    'number' => '102',
                    'description' => 'Relaxing room overlooking the Mediterranean Sea.',
                ],
                [
                    'room_type' => 'Sea View Room',
                    'number' => '103',
                    'description' => 'Modern sea view room with resort amenities.',
                ],
                [
                    'room_type' => 'Family Sea View Suite',
                    'number' => '201',
                    'description' => 'Large suite with beautiful sea views for families.',
                ],
                [
                    'room_type' => 'Family Sea View Suite',
                    'number' => '202',
                    'description' => 'Spacious family suite overlooking the Mediterranean.',
                ],
            ],

            'Mediterranean Boutique Hotel' => [
                [
                    'room_type' => 'Boutique Double Room',
                    'number' => '101',
                    'description' => 'Stylish double room with a modern boutique design.',
                ],
                [
                    'room_type' => 'Boutique Double Room',
                    'number' => '102',
                    'description' => 'Comfortable and elegant double room.',
                ],
                [
                    'room_type' => 'Premium Suite',
                    'number' => '201',
                    'description' => 'Elegant suite with extra space and premium comfort.',
                ],
                [
                    'room_type' => 'Premium Suite',
                    'number' => '202',
                    'description' => 'Luxury boutique suite with modern amenities.',
                ],
            ],

            'Istanbul Grand Hotel' => [
                [
                    'room_type' => 'Deluxe City View Room',
                    'number' => '101',
                    'description' => 'Luxury room with panoramic views of Istanbul city.',
                ],
                [
                    'room_type' => 'Deluxe City View Room',
                    'number' => '102',
                    'description' => 'Elegant room overlooking Istanbul.',
                ],
                [
                    'room_type' => 'Deluxe City View Room',
                    'number' => '103',
                    'description' => 'Modern luxury room with a beautiful city view.',
                ],
                [
                    'room_type' => 'Sultan Suite',
                    'number' => '301',
                    'description' => 'Premium luxury suite inspired by Ottoman elegance.',
                ],
                [
                    'room_type' => 'Sultan Suite',
                    'number' => '302',
                    'description' => 'Spacious suite with premium furnishings and amenities.',
                ],
            ],

            'Bosporus Residence' => [
                [
                    'room_type' => 'Bosporus View Apartment',
                    'number' => '101',
                    'description' => 'Modern apartment with a beautiful Bosporus view.',
                ],
                [
                    'room_type' => 'Bosporus View Apartment',
                    'number' => '102',
                    'description' => 'Comfortable apartment overlooking the Bosporus.',
                ],
                [
                    'room_type' => 'Family Residence',
                    'number' => '201',
                    'description' => 'Spacious residence designed for families.',
                ],
                [
                    'room_type' => 'Family Residence',
                    'number' => '202',
                    'description' => 'Large family apartment with comfortable living areas.',
                ],
            ],

            'Rome Imperial Hotel' => [
                [
                    'room_type' => 'Classic Double Room',
                    'number' => '101',
                    'description' => 'Elegant double room inspired by classic Roman design.',
                ],
                [
                    'room_type' => 'Classic Double Room',
                    'number' => '102',
                    'description' => 'Comfortable room located in the heart of Rome.',
                ],
                [
                    'room_type' => 'Classic Double Room',
                    'number' => '103',
                    'description' => 'Classic and elegant room with modern amenities.',
                ],
                [
                    'room_type' => 'Imperial Suite',
                    'number' => '301',
                    'description' => 'Luxury suite with a classic Roman-inspired design.',
                ],
                [
                    'room_type' => 'Imperial Suite',
                    'number' => '302',
                    'description' => 'Premium suite with spacious living areas.',
                ],
            ],

            'Roman Holiday Apartments' => [
                [
                    'room_type' => 'One Bedroom Apartment',
                    'number' => '101',
                    'description' => 'Comfortable apartment ideal for couples.',
                ],
                [
                    'room_type' => 'One Bedroom Apartment',
                    'number' => '102',
                    'description' => 'Modern apartment suitable for couples or small families.',
                ],
                [
                    'room_type' => 'Two Bedroom Apartment',
                    'number' => '201',
                    'description' => 'Spacious two-bedroom apartment for families.',
                ],
                [
                    'room_type' => 'Two Bedroom Apartment',
                    'number' => '202',
                    'description' => 'Comfortable family apartment with two separate bedrooms.',
                ],
            ],
        ];

        foreach ($rooms as $propertyName => $propertyRooms) {

            $property = $properties[$propertyName] ?? null;

            if (! $property) {
                continue;
            }

            foreach ($propertyRooms as $room) {

                $roomType = $property->roomTypes
                    ->firstWhere('name', $room['room_type']);

                if (! $roomType) {
                    continue;
                }

                $property->rooms()->updateOrCreate(
                    [
                        'number' => $room['number'],
                    ],
                    [
                        'room_type_id' => $roomType->id,
                        'description' => $room['description'],
                    ]
                );
            }
        }
    }
}
