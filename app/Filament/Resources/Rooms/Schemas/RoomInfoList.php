<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoomInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Section::make(__('messages.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('property.name')
                                    ->label(__('messages.property')),

                                TextEntry::make('roomType.name')
                                    ->label(__('messages.room_type')),

                                TextEntry::make('number')
                                    ->label(__('messages.room_number')),

                                TextEntry::make('roomType.capacity')
                                    ->label(__('messages.capacity'))
                                    ->suffix(' ' . __('messages.guests_count')),

                                TextEntry::make('roomType.description')
                                    ->label(__('messages.room_type_description')),

                                TextEntry::make('roomType.base_price')
                                    ->label(__('messages.price_per_night'))
                                    ->money('EGP'),

                            ]),
                    ]),

                Section::make(__('messages.room_details'))
                    ->schema([

                        TextEntry::make('description')
                            ->label(__('messages.description'))
                            ->markdown(),

                        TextEntry::make('roomType.amenities.name')
                            ->label(__('messages.amenities'))
                            ->badge()
                            ->separator(','),

                    ]),

                Section::make(__('messages.images'))
                    ->schema([

                        RepeatableEntry::make('images')
                            ->schema([

                                ImageEntry::make('image')
                                    ->label(__('messages.image'))
                                    ->disk('public'),

                                TextEntry::make('sort_order')
                                    ->label(__('messages.sort_order')),

                                TextEntry::make('is_cover')
                                    ->label(__('messages.cover_image'))
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state) => $state ? __('messages.yes') : __('messages.no'))
                                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),

                            ])
                            ->columns(3),

                    ]),
            ]);
    }
}