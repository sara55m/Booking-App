<?php

namespace App\Filament\Resources\Amenities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;


class AmenityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.amenity_details'))
                    ->label(__('messages.amenity_details'))
                    ->components([
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('icon')
                            ->label(__('messages.icon'))
                            ->options([
                                // Connectivity
                                'heroicon-o-wifi' => 'WiFi',

                                // Accommodation
                                'heroicon-o-home' => 'Apartment / Home',
                                'heroicon-o-building-office' => 'Hotel',

                                // Transport
                                'heroicon-o-truck' => 'Parking',

                                // Comfort
                                'heroicon-o-fire' => 'Heating',
                                'heroicon-o-sun' => 'Air Conditioning',

                                // Utilities
                                'heroicon-o-bolt' => 'Electricity',

                                // Entertainment
                                'heroicon-o-tv' => 'TV',

                                // Communication
                                'heroicon-o-device-phone-mobile' => 'Phone',

                                // Dining
                                'heroicon-o-cake' => 'Breakfast',
                                'heroicon-o-shopping-bag' => 'Restaurant',

                                // Wellness
                                'heroicon-o-heart' => 'Spa',
                                'heroicon-o-sparkles' => 'Luxury',

                                // Fitness
                                'heroicon-o-trophy' => 'Gym',

                                // Business
                                'heroicon-o-briefcase' => 'Business Center',

                                // Family
                                'heroicon-o-user-group' => 'Family Friendly',

                                // Security
                                'heroicon-o-shield-check' => '24/7 Security',
                                'heroicon-o-lock-closed' => 'Safe',

                                // Nature
                                'heroicon-o-photo' => 'Garden',
                                'heroicon-o-globe-alt' => 'Beach',

                                // Laundry
                                'heroicon-o-beaker' => 'Laundry',

                                // Workspace
                                'heroicon-o-computer-desktop' => 'Workspace',

                                // Accessibility
                                'heroicon-o-hand-raised' => 'Accessible',

                                // View
                                'heroicon-o-eye' => 'Scenic View',
                            ])
                            ->allowHtml()
                            ->native(false)
                            ->getOptionLabelFromRecordUsing(fn ($value): string => $value)
                            ->searchable()
                            ->required(),
                    ]),

            ]);
    }
}
