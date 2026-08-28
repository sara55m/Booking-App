<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\TravelCategory;
use Illuminate\Database\Seeder;

class CityTravelCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::get()->keyBy('slug');

        $travelCategories = TravelCategory::get()->keyBy('name');

        $cityCategories = [

            'cairo' => [
                'Culture',
                'History',
                'City Break',
                'Food',
                'Shopping',
                'Luxury',
            ],

            'alexandria' => [
                'Beach',
                'Culture',
                'History',
                'Food',
                'City Break',
            ],

            'istanbul' => [
                'Culture',
                'History',
                'City Break',
                'Food',
                'Shopping',
                'Luxury',
            ],

            'rome' => [
                'Culture',
                'History',
                'City Break',
                'Food',
                'Luxury',
            ],
        ];

        foreach ($cityCategories as $citySlug => $categoryNames) {

            $city = $cities[$citySlug] ?? null;

            if (! $city) {
                continue;
            }

            $categoryIds = [];

            foreach($categoryNames as $categoryName){
                if (! isset($travelCategories[$categoryName])) {
                    throw new \Exception(
                        "Travel Category '{$categoryName}' was not found."
                    );
                }

                $categoryIds[] = $travelCategories[$categoryName]->id;
            }

            $city->travelCategories()->sync($categoryIds);
        }
    }
}
