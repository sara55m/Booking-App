<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;


class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Room Details')
                    ->tabs([
                         Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->components([
                                    Select::make('property_id')
                                    ->label(__('messages.property'))
                                        ->relationship('property', 'name')
                                        ->required(),
                                    TextInput::make('name')
                                        ->label(__('messages.name'))
                                        ->required(),
                                    TextInput::make('number')
                                        ->label(__('messages.room_number'))
                                        ->required(),
                            ]),

                            Tab::make('Room Details')
                            ->label(__('messages.room_details'))
                            ->components([
                                TextArea::make('description')
                                    ->label(__('messages.description'))
                                    ->required(),
                                Select::make('amenities')
                                ->relationship('amenities', 'name')
                                ->preload()
                                ->searchable()
                                ->multiple(),
                                TextInput::make('price-per-night')
                                    ->label(__('messages.price_per_night'))
                                    ->required()
                                    ->numeric(),
                                TextInput::make('capacity')
                                    ->label(__('messages.capacity'))
                                    ->required()
                                    ->numeric(),
                            ]),

                    ])->columns(2)->columnSpanFull(),


            ]);
    }
}
