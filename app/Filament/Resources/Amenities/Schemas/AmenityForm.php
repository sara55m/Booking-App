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
                                'heroicon-o-wifi' => 'WiFi',
                                'heroicon-o-building-office' => 'Building',
                                'heroicon-o-truck' => 'Parking',
                                'heroicon-o-fire' => 'Heating',
                                'heroicon-o-bolt' => 'Electricity',
                                'heroicon-o-home' => 'Home',
                                'heroicon-o-sparkles' => 'Luxury',
                                'heroicon-o-tv' => 'TV',
                                'heroicon-o-phone' => 'Phone',
                                'heroicon-o-cake' => 'Breakfast',
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
