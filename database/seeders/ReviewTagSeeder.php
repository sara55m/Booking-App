<?php

namespace Database\Seeders;

use App\Models\ReviewTag;
use Illuminate\Database\Seeder;

class ReviewTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [

            // Positive tags
            [
                'name' => 'Clean',
                'type' => 'positive',
            ],
            [
                'name' => 'Comfortable',
                'type' => 'positive',
            ],
            [
                'name' => 'Friendly Staff',
                'type' => 'positive',
            ],
            [
                'name' => 'Great Location',
                'type' => 'positive',
            ],
            [
                'name' => 'Beautiful View',
                'type' => 'positive',
            ],
            [
                'name' => 'Excellent Service',
                'type' => 'positive',
            ],
            [
                'name' => 'Good Value',
                'type' => 'positive',
            ],
            [
                'name' => 'Delicious Food',
                'type' => 'positive',
            ],
            [
                'name' => 'Spacious Room',
                'type' => 'positive',
            ],
            [
                'name' => 'Quiet',
                'type' => 'positive',
            ],

            // Negative tags
            [
                'name' => 'Dirty',
                'type' => 'negative',
            ],
            [
                'name' => 'Noisy',
                'type' => 'negative',
            ],
            [
                'name' => 'Uncomfortable',
                'type' => 'negative',
            ],
            [
                'name' => 'Poor Service',
                'type' => 'negative',
            ],
            [
                'name' => 'Bad Location',
                'type' => 'negative',
            ],
            [
                'name' => 'Expensive',
                'type' => 'negative',
            ],
            [
                'name' => 'Small Room',
                'type' => 'negative',
            ],
            [
                'name' => 'Poor WiFi',
                'type' => 'negative',
            ],
            [
                'name' => 'Old Facilities',
                'type' => 'negative',
            ],
            [
                'name' => 'Bad Food',
                'type' => 'negative',
            ],

            // Neutral tags
            [
                'name' => 'Business Trip',
                'type' => 'neutral',
            ],
            [
                'name' => 'Family Stay',
                'type' => 'neutral',
            ],
            [
                'name' => 'Couple Stay',
                'type' => 'neutral',
            ],
            [
                'name' => 'Solo Travel',
                'type' => 'neutral',
            ],
            [
                'name' => 'Short Stay',
                'type' => 'neutral',
            ],
        ];

        foreach ($tags as $tag) {
            ReviewTag::updateOrCreate(
                [
                    'name' => $tag['name'],
                ],
                [
                    'type' => $tag['type'],
                    'is_active' => true,
                ]
            );
        }
    }
}
