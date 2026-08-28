<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeAmenitySeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = RoomType::with('property')
            ->get()
            ->keyBy('name');

        $amenities = Amenity::get()
            ->keyBy('name');

            $roomTypeAmenities = [

            'Deluxe Nile View Room' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Breakfast',
            ],

            'Executive Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Breakfast',
                'Gym',
                'Spa',
            ],

            'Standard Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Breakfast',
            ],

            'Family Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Family Rooms',
                'Breakfast',
            ],

            'Sea View Room' => [
                'Wi-Fi',
                'Air Conditioning',
                'Beach Access',
                'Breakfast',
            ],

            'Family Sea View Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Family Rooms',
                'Beach Access',
                'Breakfast',
            ],

            'Boutique Double Room' => [
                'Wi-Fi',
                'Air Conditioning',
                'Breakfast',
                'Non-Smoking Rooms',
            ],

            'Premium Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Spa',
            ],

            'Deluxe City View Room' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Non-Smoking Rooms',
            ],

            'Sultan Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Spa',
                'Gym',
            ],

            'Bosporus View Apartment' => [
                'Wi-Fi',
                'Air Conditioning',
                'Family Rooms',
                'Non-Smoking Rooms',
            ],

            'Family Residence' => [
                'Wi-Fi',
                'Air Conditioning',
                'Family Rooms',
                'Elevator',
            ],

            'Classic Double Room' => [
                'Wi-Fi',
                'Air Conditioning',
                'Non-Smoking Rooms',
                'Breakfast',
            ],

            'Imperial Suite' => [
                'Wi-Fi',
                'Air Conditioning',
                'Room Service',
                'Gym',
                'Spa',
            ],

            'One Bedroom Apartment' => [
                'Wi-Fi',
                'Air Conditioning',
                'Non-Smoking Rooms',
            ],

            'Two Bedroom Apartment' => [
                'Wi-Fi',
                'Air Conditioning',
                'Family Rooms',
                'Non-Smoking Rooms',
            ],

        ];

        foreach ($roomTypeAmenities as $roomTypeName => $amenityNames) {

            $roomType = $roomTypes[$roomTypeName] ?? null;

            if (! $roomType) {
                continue;
            }

            $amenityIds = [];

            foreach ($amenityNames as $amenityName) {

                if (! isset($amenities[$amenityName])) {
                    throw new \Exception(
                        "Amenity '{$amenityName}' was not found."
                    );
                }

                $amenityIds[] = $amenities[$amenityName]->id;
            }

            $roomType->amenities()
                ->syncWithoutDetaching($amenityIds);
        }
    }
}
