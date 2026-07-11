<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__("messages.payment_information"))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label(__("messages.id")),

                        TextEntry::make('booking.reference')
                            ->label(__("messages.booking")),

                        TextEntry::make('amount')
                            ->label(__("messages.amount"))
                            ->money('EGP'),

                        TextEntry::make('currency')
                        ->label(__("messages.currency"))
                            ->badge(),

                        TextEntry::make('payment_method')
                        ->label(__("messages.payment_method")),

                        TextEntry::make('status')
                            ->label(__("messages.status"))
                            ->badge(),

                        TextEntry::make('remaining')
                            ->label(__("messages.remaining_amount"))
                            ->money('EGP'),

                        TextEntry::make('paid_at')
                            ->label(__("messages.paid_at"))
                            ->dateTime(),
                    ]),

                Section::make(__("messages.stripe_information"))
                    ->schema([
                        TextEntry::make('transaction_id')
                        ->placeholder('-')
                        ->label(__("messages.transaction_id"))
                        ->copyable(),
                        TextEntry::make('stripe_session_id')
                        ->placeholder('-')
                        ->label(__("messages.stripe_session_id"))
                        ->copyable(),
                        TextEntry::make('stripe_payment_intent_id')
                        ->placeholder('-')
                        ->label(__("messages.stripe_payment_intent_id"))
                        ->copyable(),
                    ]),

                Section::make(__("messages.rewards"))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('earned_points')
                        ->label(__("messages.earned_points")),
                        TextEntry::make('redeemed_points')
                        ->label(__("messages.redeemed_points")),
                        TextEntry::make('discount_amount')
                            ->label(__("messages.discount_amount"))
                            ->money('EGP'),
                    ]),

                Section::make(__("messages.refund_information"))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('refunded_amount')
                            ->label(__("messages.refunded_amount"))
                            ->money('EGP'),

                        TextEntry::make('refunded_at')
                            ->label(__("messages.refunded_at"))
                            ->placeholder('-')
                            ->dateTime(),
                    ]),
            ]);
        
    }
}


                