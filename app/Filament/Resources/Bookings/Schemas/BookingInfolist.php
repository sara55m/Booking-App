<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('messages.booking_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('reference')
                                    ->label(__('messages.reference'))
                                    ->copyable(),

                                TextEntry::make('status')
                                    ->label(__('messages.status'))
                                    ->badge(),

                                TextEntry::make('payment_status')
                                    ->label(__('messages.payment_status'))
                                    ->badge(),

                                TextEntry::make('check_in')
                                    ->label(__('messages.check_in'))
                                    ->date(),

                                TextEntry::make('check_out')
                                    ->label(__('messages.check_out'))
                                    ->date(),

                                TextEntry::make('nights_count')
                                    ->label(__('messages.number_of_nights')),

                                TextEntry::make('guests_count')
                                    ->label(__('messages.guests_count')),

                                TextEntry::make('total_price')
                                    ->label(__('messages.total_price'))
                                    ->money('EGP'),
                            ]),
                    ]),

                Section::make(__('messages.customer_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('user.name')
                                    ->label(__('messages.name')),

                                TextEntry::make('user.email')
                                    ->label(__('messages.email')),

                                TextEntry::make('user.phone')
                                    ->label(__('messages.phone')),

                                TextEntry::make('user.reward_points')
                                    ->label(__('messages.reward_points_number')),
                            ]),
                    ]),

                Section::make(__('messages.property_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('property.name')
                                    ->label(__('messages.property_name')),

                                TextEntry::make('room.number')
                                    ->label(__('messages.room_number')),

                                TextEntry::make('room.name')
                                    ->label(__('messages.room_type')),

                                TextEntry::make('property.city.name')
                                    ->label(__('messages.city')),
                            ]),
                    ]),

                Section::make(__('messages.timestamps'))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label(__('messages.updated_at'))
                                    ->dateTime(),

                                TextEntry::make('expires_at')
                                    ->label(__('messages.expires_at'))
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('balance_due_date')
                                ->label(__('messages.balance_due_date'))
                                ->dateTime()
                                ->placeholder('-'),
                            ]),
                    ]),

                Section::make(__('messages.offer_info'))
                ->visible(fn ($record) => filled($record->offer_id))
                ->schema([
                    Grid::make(2)
                        ->schema([

                            TextEntry::make('offer.title')
                                ->label(__('messages.title')),

                            TextEntry::make('offer.code')
                                ->label(__('messages.code')),

                            TextEntry::make('offer.discount_type')
                                ->label(__('messages.discount_type'))
                                ->badge(),

                            TextEntry::make('offer.discount_value')
                            ->label(__('messages.discount_value')),

                            TextEntry::make('original_price')
                                ->money('EGP')
                                ->label(__('messages.original_price')),

                            TextEntry::make('discount_amount')
                                ->money('EGP')
                                ->label(__('messages.booking_discount_amount')),
                        ]),
                ]),

                Section::make(__('messages.invoice_info'))
                ->visible(fn ($record) => $record->invoice_number !== null)
                ->schema([
                    Grid::make(2)
                        ->schema([

                            TextEntry::make('invoice_number')
                                ->label(__('messages.invoice_number')),

                            TextEntry::make('invoice_path')
                                ->visible(fn ($record) => $record->invoice_path !== null)
                                ->label(__('messages.invoice_path')),
                        ]),
                ]),
            ]);
    }
}
