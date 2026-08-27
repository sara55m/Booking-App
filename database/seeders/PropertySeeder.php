<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\PropertyType;
use App\Models\Property;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::pluck('id', 'slug')->toArray();

        $propertyTypes = PropertyType::pluck('id', 'slug')->toArray();

        $properties = [
            [
                'name' => 'Nile Grand Hotel',
                'address' => 'Corniche El Nil, Cairo, Egypt',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'description' => 'A luxury hotel overlooking the Nile River in the heart of Cairo.',
                'rating' => 4.8,
                'is_active' => true,
                'is_featured' => true,
                'city_slug' => 'cairo',
                'property_type_slug' => 'hotel',
                'minimum_partial_payment_percentage' => 30,
            ],

            [
                'name' => 'Cairo City Suites',
                'address' => 'Downtown Cairo, Egypt',
                'latitude' => 30.0500,
                'longitude' => 31.2400,
                'description' => 'Modern suites located near Cairo attractions and shopping areas.',
                'rating' => 4.5,
                'is_active' => true,
                'is_featured' => false,
                'city_slug' => 'cairo',
                'property_type_slug' => 'apartment',
                'minimum_partial_payment_percentage' => 20,
            ],

            [
                'name' => 'Alexandria Sea View Resort',
                'address' => 'Corniche Road, Alexandria, Egypt',
                'latitude' => 31.2001,
                'longitude' => 29.9187,
                'description' => 'A relaxing seaside resort with beautiful Mediterranean views.',
                'rating' => 4.7,
                'is_active' => true,
                'is_featured' => true,
                'city_slug' => 'alexandria',
                'property_type_slug' => 'resort',
                'minimum_partial_payment_percentage' => 30,
            ],

            [
                'name' => 'Mediterranean Boutique Hotel',
                'address' => 'Stanley, Alexandria, Egypt',
                'latitude' => 31.2357,
                'longitude' => 29.9450,
                'description' => 'A charming boutique hotel close to Alexandria’s waterfront.',
                'rating' => 4.4,
                'is_active' => true,
                'is_featured' => false,
                'city_slug' => 'alexandria',
                'property_type_slug' => 'hotel',
                'minimum_partial_payment_percentage' => 25,
            ],

            [
                'name' => 'Istanbul Grand Hotel',
                'address' => 'Sultanahmet, Istanbul, Turkey',
                'latitude' => 41.0082,
                'longitude' => 28.9784,
                'description' => 'A luxury hotel located near Istanbul’s most famous landmarks.',
                'rating' => 4.9,
                'is_active' => true,
                'is_featured' => true,
                'city_slug' => 'istanbul',
                'property_type_slug' => 'hotel',
                'minimum_partial_payment_percentage' => 40,
            ],

            [
                'name' => 'Bosporus Residence',
                'address' => 'Besiktas, Istanbul, Turkey',
                'latitude' => 41.0422,
                'longitude' => 29.0063,
                'description' => 'Elegant apartments with beautiful Bosporus views.',
                'rating' => 4.6,
                'is_active' => true,
                'is_featured' => true,
                'city_slug' => 'istanbul',
                'property_type_slug' => 'apartment',
                'minimum_partial_payment_percentage' => 25,
            ],

            [
                'name' => 'Rome Imperial Hotel',
                'address' => 'Via Nazionale, Rome, Italy',
                'latitude' => 41.9028,
                'longitude' => 12.4964,
                'description' => 'An elegant hotel in central Rome near historic attractions.',
                'rating' => 4.8,
                'is_active' => true,
                'is_featured' => true,
                'city_slug' => 'rome',
                'property_type_slug' => 'hotel',
                'minimum_partial_payment_percentage' => 35,
            ],

            [
                'name' => 'Roman Holiday Apartments',
                'address' => 'Trastevere, Rome, Italy',
                'latitude' => 41.8890,
                'longitude' => 12.4694,
                'description' => 'Comfortable apartments in one of Rome’s most charming neighborhoods.',
                'rating' => 4.5,
                'is_active' => true,
                'is_featured' => false,
                'city_slug' => 'rome',
                'property_type_slug' => 'apartment',
                'minimum_partial_payment_percentage' => 20,
            ],
        ];

        foreach($properties as $property){
            Property::updateOrCreate(
                [
                    'name' => $property['name'],
                ],
                [
                    'address' => $property['address'],
                    'latitude' => $property['latitude'],
                    'longitude' => $property['longitude'],
                    'description' => $property['description'],
                    'rating' => $property['rating'],
                    'is_active' => $property['is_active'],
                    'is_featured' => $property['is_featured'],

                    'city_id' => $cities[$property['city_slug']] ?? null,

                    'property_type_id' =>
                        $propertyTypes[$property['property_type_slug']] ?? null,

                    'minimum_partial_payment_percentage' =>
                        $property['minimum_partial_payment_percentage'],
                ]
            );
        }
    }
}
