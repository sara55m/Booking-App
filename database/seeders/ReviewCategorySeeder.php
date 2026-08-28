<?php

namespace Database\Seeders;

use App\Models\ReviewCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cleanliness',
            'Comfort',
            'Location',
            'Facilities',
            'Staff',
            'Value for Money',
        ];

        foreach ($categories as $index => $category) {
            ReviewCategory::updateOrCreate(
                [
                    'slug' => Str::slug($category),
                ],
                [
                    'name' => $category,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
