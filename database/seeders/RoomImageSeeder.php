<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomImageSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::with([
            'property',
            'roomType',
        ])->get();

        $images = [

            'Nile Grand Hotel' => [

                '101' => [
                    [
                        'image' => 'rooms/nile-deluxe-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Deluxe Nile View Room',
                    ],
                    [
                        'image' => 'rooms/nile-deluxe-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Nile River view from the room',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/nile-executive-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Executive Suite',
                    ],
                    [
                        'image' => 'rooms/nile-executive-201-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Executive suite living area',
                    ],
                ],
            ],

            'Cairo City Suites' => [

                '101' => [
                    [
                        'image' => 'rooms/cairo-standard-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Standard Suite',
                    ],
                    [
                        'image' => 'rooms/cairo-standard-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Modern suite interior',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/cairo-family-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Family Suite',
                    ],
                    [
                        'image' => 'rooms/cairo-family-201-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Spacious family suite',
                    ],
                ],
            ],

            'Alexandria Sea View Resort' => [

                '101' => [
                    [
                        'image' => 'rooms/alexandria-sea-view-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Sea View Room',
                    ],
                    [
                        'image' => 'rooms/alexandria-sea-view-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Mediterranean Sea view',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/alexandria-family-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Family Sea View Suite',
                    ],
                    [
                        'image' => 'rooms/alexandria-family-201-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Family suite with sea view',
                    ],
                ],
            ],

            'Mediterranean Boutique Hotel' => [

                '101' => [
                    [
                        'image' => 'rooms/boutique-double-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Boutique Double Room',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/boutique-premium-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Premium Suite',
                    ],
                ],
            ],

            'Istanbul Grand Hotel' => [

                '101' => [
                    [
                        'image' => 'rooms/istanbul-deluxe-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Deluxe City View Room',
                    ],
                    [
                        'image' => 'rooms/istanbul-deluxe-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Istanbul city view',
                    ],
                ],

                '301' => [
                    [
                        'image' => 'rooms/istanbul-sultan-301-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Sultan Suite',
                    ],
                    [
                        'image' => 'rooms/istanbul-sultan-301-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Luxury Sultan Suite interior',
                    ],
                ],
            ],

            'Bosporus Residence' => [

                '101' => [
                    [
                        'image' => 'rooms/bosporus-view-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Bosporus View Apartment',
                    ],
                    [
                        'image' => 'rooms/bosporus-view-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Beautiful Bosporus view',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/bosporus-family-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Family Residence',
                    ],
                ],
            ],

            'Rome Imperial Hotel' => [

                '101' => [
                    [
                        'image' => 'rooms/rome-classic-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Classic Double Room',
                    ],
                    [
                        'image' => 'rooms/rome-classic-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Classic Roman-inspired interior',
                    ],
                ],

                '301' => [
                    [
                        'image' => 'rooms/rome-imperial-301-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Imperial Suite',
                    ],
                    [
                        'image' => 'rooms/rome-imperial-301-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Luxury Imperial Suite',
                    ],
                ],
            ],

            'Roman Holiday Apartments' => [

                '101' => [
                    [
                        'image' => 'rooms/rome-one-bedroom-101-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'One Bedroom Apartment',
                    ],
                    [
                        'image' => 'rooms/rome-one-bedroom-101-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Comfortable apartment interior',
                    ],
                ],

                '201' => [
                    [
                        'image' => 'rooms/rome-two-bedroom-201-1.jpg',
                        'is_cover' => true,
                        'sort_order' => 0,
                        'caption' => 'Two Bedroom Apartment',
                    ],
                    [
                        'image' => 'rooms/rome-two-bedroom-201-2.jpg',
                        'is_cover' => false,
                        'sort_order' => 1,
                        'caption' => 'Spacious family apartment',
                    ],
                ],
            ],
        ];

        foreach ($images as $propertyName => $propertyRooms) {

            foreach ($propertyRooms as $roomNumber => $roomImages) {

                $room = $rooms->first(function ($room) use (
                    $propertyName,
                    $roomNumber
                ) {
                    return $room->property?->name === $propertyName
                        && (string) $room->number === (string) $roomNumber;
                });

                if (! $room) {
                    continue;
                }

                foreach ($roomImages as $image) {

                    $room->images()->updateOrCreate(
                        [
                            'image' => $image['image'],
                        ],
                        [
                            'is_cover' => $image['is_cover'],
                            'sort_order' => $image['sort_order'],
                            'caption' => $image['caption'],
                        ]
                    );
                }
            }
        }
    }
}
