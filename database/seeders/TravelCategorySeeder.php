<?php

namespace Database\Seeders;

use App\Models\TravelCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TravelCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            [
                'name' => 'Beach',
                'icon' => 'heroicon-o-sun',
                'description' => 'Relax and enjoy beautiful beaches and coastal destinations.',
            ],

            [
                'name' => 'Culture',
                'icon' => 'heroicon-o-building-library',
                'description' => 'Discover local culture, traditions, art, and unique experiences.',
            ],

            [
                'name' => 'History',
                'icon' => 'heroicon-o-building-office-2',
                'description' => 'Explore historical landmarks, ancient sites, and famous monuments.',
            ],

            [
                'name' => 'Nature',
                'icon' => 'heroicon-o-globe-alt',
                'description' => 'Enjoy natural landscapes, mountains, parks, and outdoor destinations.',
            ],

            [
                'name' => 'City Break',
                'icon' => 'heroicon-o-building-office',
                'description' => 'Perfect destinations for short city trips and urban adventures.',
            ],

            [
                'name' => 'Luxury',
                'icon' => 'heroicon-o-sparkles',
                'description' => 'Experience premium hotels, exclusive destinations, and luxury travel.',
            ],

            [
                'name' => 'Family',
                'icon' => 'heroicon-o-user-group',
                'description' => 'Family-friendly destinations with activities suitable for everyone.',
            ],

            [
                'name' => 'Adventure',
                'icon' => 'heroicon-o-fire',
                'description' => 'Enjoy exciting activities, outdoor adventures, and thrilling experiences.',
            ],

            [
                'name' => 'Food',
                'icon' => 'heroicon-o-cake',
                'description' => 'Discover local cuisine, restaurants, and unforgettable food experiences.',
            ],

            [
                'name' => 'Shopping',
                'icon' => 'heroicon-o-shopping-bag',
                'description' => 'Explore shopping destinations, local markets, and popular stores.',
            ],
        ];

        foreach ($categories as $index => $category) {

            TravelCategory::updateOrCreate(
                [
                    'slug' => Str::slug($category['name']),
                ],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
