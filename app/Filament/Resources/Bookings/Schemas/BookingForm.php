<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Enums\BookingPaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use App\Models\Room;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;

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
                                TextInput::make('guests_count')
                                ->label(__('messages.guests_count'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(fn ($record) => $record?->room?->capacity),
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

                                DatePicker::make('expires_at')
                                    ->label(__('messages.expires_at'))
                                    ->nullable(),

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
