<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Egypt',
                'iso_code' => 'EG',
                'flag' => 'countries/egypt.png',
                'currency' => 'EGP',
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Arabia',
                'iso_code' => 'SA',
                'flag' => 'countries/saudi_arabia.png',
                'currency' => 'SAR',
                'is_active' => true,
            ],
            [
                'name' => 'United Arab Emirates',
                'iso_code' => 'AE',
                'flag' => 'countries/united_arab_emirates.png',
                'currency' => 'AED',
                'is_active' => true,
            ],
            [
                'name' => 'Turkey',
                'iso_code' => 'TR',
                'flag' => 'countries/turkey.png',
                'currency' => 'TRY',
                'is_active' => true,
            ],
            [
                'name' => 'Italy',
                'iso_code' => 'IT',
                'flag' => 'countries/italy.jpeg',
                'currency' => 'EUR',
                'is_active' => true,
            ],
            [
                'name' => 'Qatar',
                'iso_code' => 'QA',
                'flag' => 'countries/qatar.png',
                'currency' => 'QAR',
                'is_active' => true,
            ],
            [
                'name' => 'Kuwait',
                'iso_code' => 'KW',
                'flag' => 'countries/kuwait.png',
                'currency' => 'KWD',
                'is_active' => true,
            ],
            [
                'name' => 'Jordan',
                'iso_code' => 'JO',
                'flag' => 'countries/jordan.png',
                'currency' => 'JOD',
                'is_active' => true,
            ],
            [
                'name' => 'Morocco',
                'iso_code' => 'MA',
                'flag' => 'countries/morocco.png',
                'currency' => 'MAD',
                'is_active' => true,
            ],
            [
                'name' => 'France',
                'iso_code' => 'FR',
                'flag' => 'countries/france.png',
                'currency' => 'EUR',
                'is_active' => true,
            ],
            [
                'name' => 'Spain',
                'iso_code' => 'ES',
                'flag' => 'countries/spain.png',
                'currency' => 'EUR',
                'is_active' => true,
            ],
            [
                'name' => 'Germany',
                'iso_code' => 'DE',
                'flag' => 'countries/germany.png',
                'currency' => 'EUR',
                'is_active' => true,
            ],
            [
                'name' => 'United Kingdom',
                'iso_code' => 'GB',
                'flag' => 'countries/united_kingdom.png',
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'name' => 'United States',
                'iso_code' => 'US',
                'flag' => 'countries/united_states.png',
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Thailand',
                'iso_code' => 'TH',
                'flag' => 'countries/thailand.png',
                'currency' => 'THB',
                'is_active' => true,
            ],
            [
                'name' => 'Indonesia',
                'iso_code' => 'ID',
                'flag' => 'countries/indonesia.png',
                'currency' => 'IDR',
                'is_active' => true,
            ],
        ];

        foreach($countries as $country){
            Country::updateOrCreate
            (
                [
                    'iso_code'=>$country['iso_code'],
                ],
                [
                    'name' => $country['name'],
                    'flag' =>  $country['flag'],
                    'currency' =>  $country['currency'],
                    'is_active' =>  $country['is_active'],
                ]
            );
        }
    }
}
