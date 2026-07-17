<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Enums\OfferStatus;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Schema;

class OfferInfolist
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

                                Section::make()
                                    ->schema([

                                        TextEntry::make('property.name')
                                            ->label(__('messages.property'))
                                            ->placeholder('-'),

                                        TextEntry::make('title')
                                            ->label(__('messages.title')),

                                        TextEntry::make('computed_status')
                                            ->label(__('messages.status'))
                                            ->badge()
                                            ->formatStateUsing(fn (OfferStatus $state) => $state->label())
                                            ->color(fn (OfferStatus $state) => $state->color()),

                                        IconEntry::make('requires_coupon_code')
                                            ->label(__('messages.requires_coupon_code'))
                                            ->boolean(),

                                        TextEntry::make('code')
                                            ->label(__('messages.code'))
                                            ->placeholder('-'),

                                    ])
                                    ->columns(2),
                            ]),

                        /*
                        |--------------------------------------------------------------------------
                        | Usage Information
                        |--------------------------------------------------------------------------
                        */

                        Tab::make(__('messages.usage_info'))
                            ->schema([

                                Section::make()
                                    ->schema([

                                        TextEntry::make('usage_limit')
                                            ->label(__('messages.usage_limit'))
                                            ->placeholder(__('messages.unlimited')),

                                        TextEntry::make('used_count')
                                            ->label(__('messages.used_count')),

                                        TextEntry::make('per_user_limit')
                                            ->label(__('messages.per_user_limit'))
                                            ->placeholder(__('messages.unlimited')),

                                    ])
                                    ->columns(3),
                            ]),

                        /*
                        |--------------------------------------------------------------------------
                        | Discount Settings
                        |--------------------------------------------------------------------------
                        */

                        Tab::make(__('messages.discount_settings'))
                            ->schema([

                                Section::make()
                                    ->schema([

                                        TextEntry::make('discount_type')
                                            ->label(__('messages.discount_type'))
                                            ->badge(),

                                        TextEntry::make('discount_value')
                                            ->label(__('messages.discount_value')),

                                    ])
                                    ->columns(3),
                            ]),

                        /*
                        |--------------------------------------------------------------------------
                        | Conditions
                        |--------------------------------------------------------------------------
                        */

                        Tab::make(__('messages.conditions'))
                            ->schema([

                                Section::make()
                                    ->schema([

                                        TextEntry::make('minimum_booking_amount')
                                            ->label(__('messages.minimum_booking_amount'))
                                            ->money('USD')
                                            ->placeholder('-'),

                                        TextEntry::make('minimum_nights')
                                            ->label(__('messages.minimum_nights'))
                                            ->placeholder('-'),

                                        IconEntry::make('is_active')
                                            ->label(__('messages.is_active'))
                                            ->boolean(),

                                        IconEntry::make('notify_users')
                                            ->label(__('messages.notify_users'))
                                            ->boolean(),

                                        TextEntry::make('notification_sent_at')
                                            ->label(__('messages.notification_sent_at'))
                                            ->since()
                                            ->placeholder(__('messages.never')),

                                    ])
                                    ->columns(2),
                            ]),

                        /*
                        |--------------------------------------------------------------------------
                        | Schedule
                        |--------------------------------------------------------------------------
                        */

                        Tab::make(__('messages.schedule'))
                            ->schema([

                                Section::make()
                                    ->schema([

                                        TextEntry::make('starts_at')
                                            ->label(__('messages.starts_at'))
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('ends_at')
                                            ->label(__('messages.ends_at'))
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('created_at')
                                            ->label(__('messages.created_at'))
                                            ->since(),

                                        TextEntry::make('updated_at')
                                            ->label(__('messages.updated_at'))
                                            ->since(),

                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}