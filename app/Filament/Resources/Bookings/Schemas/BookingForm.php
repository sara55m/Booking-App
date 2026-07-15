<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Carbon\Carbon;
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (filled($get('balance_due_date'))) {
                                            return; // don't clobber an admin's manual override
                                        }
                                    
                                        $set('balance_due_date', $state
                                            ? Carbon::parse($state)->subDays(3)->toDateString()
                                            : null);
                                    }),
                                DatePicker::make('check_out')
                                    ->label(__('messages.check_out'))
                                    ->required()
                                    ->minDate(fn($context) => $context === 'create' ? now() : null)
                                    ->afterOrEqual('check_in'),

                                DatePicker::make('expires_at')
                                    ->label(__('messages.expires_at'))
                                    ->helperText(__('messages.expires_at_help'))
                                    ->nullable(),

                                DatePicker::make('balance_due_date')
                                ->label(__('messages.balance_due_date'))
                                ->helperText(__('messages.balance_due_date_help'))
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
