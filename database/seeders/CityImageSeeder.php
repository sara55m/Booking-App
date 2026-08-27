<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CityImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'cairo' => [
                [
                    'image' => 'cities/cairo-1.jpeg',
                    'is_cover' => true,
                    'sort_order' => 1,
                    'caption' => 'Cairo city',
                ],
                [
                    'image' => 'cities/cairo-2.jpeg',
                    'is_cover' => false,
                    'sort_order' => 2,
                    'caption' => 'Cairo landmarks',
                ],
                [
                    'image' => 'cities/cairo-3.jpg',
                    'is_cover' => false,
                    'sort_order' => 3,
                    'caption' => 'Cairo at night',
                ],
            ],

            'alexandria' => [
                [
                    'image' => 'cities/alexandria-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 1,
                    'caption' => 'Alexandria coast',
                ],
                [
                    'image' => 'cities/alexandria-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 2,
                    'caption' => 'Alexandria waterfront',
                ],
            ],

            'istanbul' => [
                [
                    'image' => 'cities/istanbul-1.jpg',
                    'is_cover' => true,
                    'sort_order' => 1,
                    'caption' => 'Istanbul city',
                ],
                [
                    'image' => 'cities/istanbul-2.jpg',
                    'is_cover' => false,
                    'sort_order' => 2,
                    'caption' => 'Istanbul landmarks',
                ],
            ],
        ];

        foreach($cities as $slug => $images){
            $city=City::where('slug',$slug)->first();

            if(! $city){
                continue;
            }

            foreach($images as $image){
                $city->images()->updateOrCreate(
                    [
                        'image'=>$image['image'],
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
