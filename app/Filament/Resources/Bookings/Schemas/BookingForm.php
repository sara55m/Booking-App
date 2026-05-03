<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use App\Models\Room;
use Filament\Schemas\Components\Utilities\Get;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Booking Details')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->components([
                                Select::make('user_id')
                                    ->label(__('messages.user'))
                                    ->relationship('user','name')
                                    ->required(),

                                Select::make('property_id')
                                    ->label(__('messages.property'))
                                    ->relationship('property', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(fn ($set) => $set('room_id', null)),

                                Select::make('room_id')
                                    ->label(__('messages.room'))
                                    ->options(fn (Get $get) =>
                                        Room::query()
                                            ->where('property_id', $get('property_id'))
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn (Get $get) => blank($get('property_id')))
                            ]),
                        Tab::make('Dates & Status')
                            ->label(__('messages.dates_status'))
                            ->components([
                                DatePicker::make('check_in')
                                    ->label(__('messages.check_in'))
                                    ->minDate(fn ($context) => $context === 'create' ? now() : null)
                                    ->required(),
                                DatePicker::make('check_out')
                                    ->label(__('messages.check_out'))
                                    ->required()
                                    ->minDate(fn($context) => $context === 'create' ? now() : null)
                                    ->afterOrEqual('check_in'),

                                Select::make('status')
                                    ->label(__('messages.status'))
                                    ->options(BookingStatus::class)
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
