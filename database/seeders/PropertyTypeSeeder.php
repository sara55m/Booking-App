<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PropertyType;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            [
                'name' => 'Hotel',
                'image' => null,
            ],
            [
                'name' => 'Resort',
                'image' => null,
            ],
            [
                'name' => 'Apartment',
                'image' => null,
            ],
            [
                'name' => 'Villa',
                'image' => null,
            ],
            [
                'name' => 'Guest House',
                'image' => null,
            ],
            [
                'name' => 'Hostel',
                'image' => null,
            ],
            [
                'name' => 'Chalet',
                'image' => null,
            ],
            [
                'name' => 'Lodge',
                'image' => null,
            ],
            [
                'name' => 'Cabin',
                'image' => null,
            ],
        ];

        foreach($propertyTypes as $index => $propertyType){
            PropertyType::updateOrCreate(
                [
                    'slug'=>Str::slug($propertyType['name']),
                ],
                [
                    'name' => $propertyType['name'],
                    'image' => $propertyType['image'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
