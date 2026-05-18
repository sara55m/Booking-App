<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Offer Tabs')
                ->tabs([

                    /*
                    |--------------------------------------------------------------------------
                    | Basic Information
                    |--------------------------------------------------------------------------
                    */

                    Tab::make(__('messages.basic_info'))
                        ->schema([

                            Select::make('property_id')
                                ->relationship('property', 'name')
                                ->label(__('messages.property'))
                                ->searchable()
                                ->preload()
                                ->nullable(),

                            TextInput::make('title')
                                ->required()
                                ->label(__('messages.title'))
                                ->maxLength(255),

                            Toggle::make('requires_coupon_code')
                                ->live()
                                ->default(false)
                                ->label(__('messages.requires_coupon_code')),

                            TextInput::make('code')
                                ->unique(ignoreRecord: true)
                                ->visible(fn ($get) =>
                                    $get('requires_coupon_code'))
                                ->required(fn ($get) =>
                                    $get('requires_coupon_code'))
                                ->maxLength(255)
                                ->label(__('messages.code')),
                        ])
                        ->columns(2),

                        Tab::make(__("messages.usage_info"))
                        ->schema([
                            TextInput::make('usage_limit')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->nullable()
                                ->label(__('messages.usage_limit')),

                            TextInput::make('per_user_limit')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->nullable()
                                ->label(__('messages.per_user_limit')),
                        ])->columns(2),

                    /*
                    |--------------------------------------------------------------------------
                    | Discount Settings
                    |--------------------------------------------------------------------------
                    */

                    Tab::make(__('messages.discount_settings'))
                        ->schema([

                            Select::make('discount_type')
                                ->options([
                                    'fixed' => __('messages.fixed'),
                                    'percentage' => __('messages.percentage'),
                                ])
                                ->label(__('messages.discount_type'))
                                ->required()
                                ->live(),

                            TextInput::make('discount_value')
                                ->required()
                                ->label(__('messages.discount_value'))
                                ->numeric()
                                ->minValue(0.01)
                                ->rule(function ($get) {

                                    return $get('discount_type') === 'percentage'
                                        ? 'max:100'
                                        : null;
                                }),
                        ])
                        ->columns(2),

                    /*
                    |--------------------------------------------------------------------------
                    | Conditions
                    |--------------------------------------------------------------------------
                    */

                    Tab::make(__('messages.conditions'))
                        ->schema([

                            TextInput::make('minimum_booking_amount')
                                ->numeric()
                                ->nullable()
                                ->label(__('messages.minimum_booking_amount'))
                                ->minValue(0),

                            TextInput::make('minimum_nights')
                                ->numeric()
                                ->nullable()
                                ->label(__('messages.minimum_nights'))
                                ->integer()
                                ->minValue(1),

                            Toggle::make('is_active')
                                ->required()
                                ->default(true)
                                ->label(__('messages.is_active')),
                        ])
                        ->columns(2),

                    /*
                    |--------------------------------------------------------------------------
                    | Schedule
                    |--------------------------------------------------------------------------
                    */

                    Tab::make(__('messages.schedule'))
                        ->schema([

                            DateTimePicker::make('starts_at')
                                ->nullable()
                                ->live()
                                ->label(__('messages.starts_at'))
                                ->afterOrEqual(today()),

                            DateTimePicker::make('ends_at')
                                ->nullable()
                                ->label(__('messages.ends_at'))
                                ->after('starts_at'),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
            ]);
    }
}
