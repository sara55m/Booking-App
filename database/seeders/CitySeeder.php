<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Egypt
            [
                'country' => 'EG',
                'name' => 'Cairo',
                'description' => 'Explore the rich history, culture, and vibrant life of Egypt’s capital.',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'is_featured' => true,
            ],
            [
                'country' => 'EG',
                'name' => 'Alexandria',
                'description' => 'A beautiful Mediterranean city known for its history, beaches, and culture.',
                'latitude' => 31.2001,
                'longitude' => 29.9187,
                'is_featured' => true,
            ],
            [
                'country' => 'EG',
                'name' => 'Sharm El Sheikh',
                'description' => 'A popular Red Sea destination famous for beaches, resorts, and diving.',
                'latitude' => 27.9158,
                'longitude' => 34.3300,
                'is_featured' => true,
            ],
            [
                'country' => 'EG',
                'name' => 'Hurghada',
                'description' => 'A Red Sea resort destination offering beaches, diving, and water activities.',
                'latitude' => 27.2579,
                'longitude' => 33.8116,
                'is_featured' => true,
            ],
            [
                'country' => 'EG',
                'name' => 'Luxor',
                'description' => 'Discover ancient Egyptian temples, monuments, and the Valley of the Kings.',
                'latitude' => 25.6872,
                'longitude' => 32.6396,
                'is_featured' => false,
            ],

            // Saudi Arabia
            [
                'country' => 'SA',
                'name' => 'Riyadh',
                'description' => 'The modern capital of Saudi Arabia with a growing tourism scene.',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'is_featured' => true,
            ],
            [
                'country' => 'SA',
                'name' => 'Jeddah',
                'description' => 'A vibrant coastal city on the Red Sea known for its waterfront and historic district.',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'is_featured' => true,
            ],

            // UAE
            [
                'country' => 'AE',
                'name' => 'Dubai',
                'description' => 'A world-famous destination known for luxury hotels, shopping, and modern architecture.',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'is_featured' => true,
            ],
            [
                'country' => 'AE',
                'name' => 'Abu Dhabi',
                'description' => 'The capital of the UAE, offering luxury, culture, and beautiful beaches.',
                'latitude' => 24.4539,
                'longitude' => 54.3773,
                'is_featured' => true,
            ],

            // Turkey
            [
                'country' => 'TR',
                'name' => 'Istanbul',
                'description' => 'A unique city connecting Europe and Asia with rich history and culture.',
                'latitude' => 41.0082,
                'longitude' => 28.9784,
                'is_featured' => true,
            ],
            [
                'country' => 'TR',
                'name' => 'Antalya',
                'description' => 'A Mediterranean destination famous for beaches, resorts, and ancient landmarks.',
                'latitude' => 36.8969,
                'longitude' => 30.7133,
                'is_featured' => true,
            ],
             // Italy
             [
                'country' => 'IT',
                'name' => 'Rome',
                'description' => 'The historic capital of Italy, filled with iconic landmarks and ancient architecture.',
                'latitude' => 41.9028,
                'longitude' => 12.4964,
                'is_featured' => true,
            ],
            [
                'country' => 'IT',
                'name' => 'Milan',
                'description' => 'A global center for fashion, design, business, and culture.',
                'latitude' => 45.4642,
                'longitude' => 9.1900,
                'is_featured' => false,
            ],
             // France
             [
                'country' => 'FR',
                'name' => 'Paris',
                'description' => 'The iconic French capital known for art, culture, fashion, and historic landmarks.',
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'is_featured' => true,
            ],

            // Spain
            [
                'country' => 'ES',
                'name' => 'Barcelona',
                'description' => 'A Mediterranean city famous for architecture, beaches, and vibrant culture.',
                'latitude' => 41.3874,
                'longitude' => 2.1686,
                'is_featured' => true,
            ],
            [
                'country' => 'ES',
                'name' => 'Madrid',
                'description' => 'The lively capital of Spain, known for museums, food, and nightlife.',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'is_featured' => false,
            ],
            // United Kingdom
            [
                'country' => 'GB',
                'name' => 'London',
                'description' => 'A global city filled with history, culture, entertainment, and iconic landmarks.',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'is_featured' => true,
            ],

            // Thailand
            [
                'country' => 'TH',
                'name' => 'Bangkok',
                'description' => 'A vibrant destination known for temples, food, nightlife, and modern city life.',
                'latitude' => 13.7563,
                'longitude' => 100.5018,
                'is_featured' => true,
            ],
            [
                'country' => 'TH',
                'name' => 'Phuket',
                'description' => 'A tropical island destination famous for beaches, resorts, and nightlife.',
                'latitude' => 7.8804,
                'longitude' => 98.3923,
                'is_featured' => true,
            ],
        ];


        foreach($cities as $city){
            $country=Country::where('iso_code',$city['country'])->first();

            if(!$country){
                continue;
            }

            City::updateOrCreate(
                [
                    'country_id'=>$country->id,
                    'slug'=>Str::slug($city['name']),
                ],
                [
                    'name' => $city['name'],
                    'is_active' => true,
                    'is_featured' => $city['is_featured'],
                    'description' => $city['description'],
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                ]
            );
        }
    }
}
