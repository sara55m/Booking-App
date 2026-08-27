<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            [
                'name' => 'Wi-Fi',
                'icon' => 'heroicon-o-wifi',
            ],
            [
                'name' => 'Swimming Pool',
                'icon' => 'heroicon-o-globe-alt',
            ],
            [
                'name' => 'Parking',
                'icon' => 'heroicon-o-truck',
            ],
            [
                'name' => 'Breakfast',
                'icon' => 'heroicon-o-cake',
            ],
            [
                'name' => 'Gym',
                'icon' => 'heroicon-o-trophy',
            ],
            [
                'name' => 'Spa',
                'icon' => 'heroicon-o-heart',
            ],
            [
                'name' => 'Restaurant',
                'icon' => 'heroicon-o-shopping-bag',
            ],
            [
                'name' => 'Airport Shuttle',
                'icon' => 'heroicon-o-hand-raised',
            ],
            [
                'name' => 'Room Service',
                'icon' => 'heroicon-o-beaker',
            ],
            [
                'name' => 'Air Conditioning',
                'icon' => 'heroicon-o-sun',
            ],
            [
                'name' => 'Laundry Service',
                'icon' => 'shirt',
            ],
            [
                'name' => 'Pet Friendly',
                'icon' => 'paw-print',
            ],
            [
                'name' => 'Beach Access',
                'icon' => 'waves',
            ],
            [
                'name' => 'Conference Room',
                'icon' => 'heroicon-o-building-office',
            ],
            [
                'name' => '24-Hour Front Desk',
                'icon' => 'heroicon-o-clock',
            ],
            [
                'name' => 'Elevator',
                'icon' => 'heroicon-o-arrow-up',
            ],
            [
                'name' => 'Family Rooms',
                'icon' => 'heroicon-o-user-group',
            ],
            [
                'name' => 'Non-Smoking Rooms',
                'icon' => 'heroicon-o-no-symbol',
            ],
            [
                'name' => 'Wheelchair Accessible',
                'icon' => 'heroicon-o-user',
            ],
            [
                'name' => 'Bar',
                'icon' => 'heroicon-o-cake',
            ],
        ];

        foreach($amenities as $amenity){
            Amenity::updateOrCreate(
                [
                    'name'=>$amenity['name'],
                ],
                [
                    'icon'=>$amenity['icon']
                ]
            );
        }
    }
}
