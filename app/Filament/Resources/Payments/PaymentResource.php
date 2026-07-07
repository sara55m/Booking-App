<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.payments');
    }
    protected static ?int $navigationSort = 8;

    public static function getModelLabel(): string
    {
        return __('messages.payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.payments');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.payments');
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__("messages.payment_information"))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label(__("messages.id")),

                        TextEntry::make('booking.id')
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


    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }
}
