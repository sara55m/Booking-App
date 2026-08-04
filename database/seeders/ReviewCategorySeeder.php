<?php

namespace Database\Seeders;

use App\Models\ReviewCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories=[
            [
                'name' => 'Cleanliness',
                'slug' => 'cleanliness',
                'sort_order' => 1,
            ],
            [
                'name' => 'Location',
                'slug' => 'location',
                'sort_order' => 2,
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'sort_order' => 3,
            ],
            [
                'name' => 'Comfort',
                'slug' => 'comfort',
                'sort_order' => 4,
            ],
            [
                'name' => 'Facilities',
                'slug' => 'facilities',
                'sort_order' => 5,
            ],
            [
                'name' => 'Value for Money',
                'slug' => 'value',
                'sort_order' => 6,
            ],
            [
                'name' => 'Wi-Fi',
                'slug' => 'wifi',
                'sort_order' => 7,
            ],
        ];

        //create categories with these database
        foreach($categories as $category){
            ReviewCategory::create($category);
        }
    }
}
