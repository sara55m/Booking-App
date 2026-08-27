<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Property;

class PropertyImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::get()->keyBy('name');

        $images = [

            'Nile Grand Hotel' => [
                [
                    'image' => 'properties/nile-grand-hotel-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Nile Grand Hotel',
                ],
                [
                    'image' => 'properties/nile-grand-hotel-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Luxury room at Nile Grand Hotel',
                ],
                [
                    'image' => 'properties/nile-grand-hotel-3.jpg',
                    'is_cover' => false,
                    'sort_order' => 2,
                    'caption' => 'Nile River view',
                ],
            ],

            'Cairo City Suites' => [
                [
                    'image' => 'properties/cairo-city-suites-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Cairo City Suites',
                ],
                [
                    'image' => 'properties/cairo-city-suites-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Modern suite',
                ],
            ],

            'Alexandria Sea View Resort' => [
                [
                    'image' => 'properties/alexandria-sea-view-resort-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Alexandria Sea View Resort',
                ],
                [
                    'image' => 'properties/alexandria-sea-view-resort-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Mediterranean Sea view',
                ],
            ],

            'Mediterranean Boutique Hotel' => [
                [
                    'image' => 'properties/mediterranean-boutique-hotel-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Mediterranean Boutique Hotel',
                ],
                [
                    'image' => 'properties/mediterranean-boutique-hotel-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Boutique hotel interior',
                ],
            ],

            'Istanbul Grand Hotel' => [
                [
                    'image' => 'properties/istanbul-grand-hotel-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Istanbul Grand Hotel',
                ],
                [
                    'image' => 'properties/istanbul-grand-hotel-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Luxury hotel room',
                ],
            ],

            'Bosporus Residence' => [
                [
                    'image' => 'properties/bosporus-residence-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Bosporus Residence',
                ],
                [
                    'image' => 'properties/bosporus-residence-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Bosporus view apartment',
                ],
            ],

            'Rome Imperial Hotel' => [
                [
                    'image' => 'properties/rome-imperial-hotel-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Rome Imperial Hotel',
                ],
                [
                    'image' => 'properties/rome-imperial-hotel-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Elegant hotel interior',
                ],
            ],

            'Roman Holiday Apartments' => [
                [
                    'image' => 'properties/roman-holiday-apartments-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 0,
                    'caption' => 'Roman Holiday Apartments',
                ],
                [
                    'image' => 'properties/roman-holiday-apartments-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 1,
                    'caption' => 'Apartment interior',
                ],
            ],
        ];

        foreach($images as $propertyName => $propertyImages){
            $property=$properties[$propertyName] ?? null;

            if(! $property){
                continue;
            }

            foreach ($propertyImages as $image) {
                $property->images()->updateOrCreate(
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
