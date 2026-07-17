<?php

namespace App\Filament\Resources\RoomTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class RoomTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Room Type Details')
                    ->tabs([
                         Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->components([
                                Select::make('property_id')
                                    ->relationship('property', 'name')
                                    ->label(__("messages.property"))
                                    ->required(),
                                
                                TextInput::make('name')
                                    ->label(__("messages.name"))
                                    ->required()
                                    ->maxLength(255),
                                
                                TextInput::make('capacity')
                                    ->label(__("messages.capacity"))
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                    
                                TextInput::make('base_price')
                                    ->label(__("messages.price_per_night"))
                                    ->numeric()
                                    ->prefix('EGP')
                                    ->minValue(0)
                                    ->required(),
                            ]),
                        Tab::make('Room Type Details')
                        ->label(__('messages.room_type_details'))
                        ->components([
                            Textarea::make('description')
                                ->label(__("messages.description"))
                                ->rows(4),

                            Select::make('amenities')
                                ->relationship('amenities', 'name')
                                ->preload()
                                ->searchable()
                                ->multiple(),
                        ]),

                    ])->columns(2)->columnSpanFull(),

            ]);
    }
}
