<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\Booking;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Hidden;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Payment Details')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->components([
                                Select::make('booking_id')
                                    ->label(__('messages.booking'))
                                    ->options(
                                        Booking::query()
                                            ->get()
                                            ->mapWithKeys(fn ($booking) => [
                                                $booking->id => "Booking #{$booking->id} - {$booking->total_price} EGP"
                                            ])
                                    )
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set, callable $get) {

                                        if (!$state) return;

                                        $booking = Booking::with('payments')->find($state);

                                        if ($booking) {

                                            $currentPaymentId = $get('id');

                                            $totalPaid = $booking->payments
                                                ->where('id', '!=', $currentPaymentId)
                                                ->sum('amount');

                                            $set('total_amount', $booking->total_price);
                                            $set('already_paid', $totalPaid);

                                            // include current payment value
                                            $currentPayment = $get('payment_amount') ?? 0;

                                            $set('remaining', $booking->total_price - ($totalPaid + $currentPayment));
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                        $booking = Booking::with('payments')->find($state);

                                        if ($booking) {

                                            //in edit
                                            $currentPaymentId = $get('id');
                                            $totalPaid = $booking->payments
                                            ->where('id', '!=', $currentPaymentId) //exclude current payment in edit
                                            ->sum('amount');

                                            $set('total_amount', $booking->total_price);
                                            $set('already_paid', $totalPaid);
                                            $set('remaining', $booking->total_price - $totalPaid);
                                        }
                                    }),
                                TextInput::make('total_amount')
                                    ->label(__('messages.total_amount'))
                                    ->required()
                                    ->disabled(fn (Get $get) => blank($get('booking_id')))
                                    ->prefix('EGP')
                                    ->rule(function (callable $get) {
                                        return function ($attribute, $value, $fail) use ($get) {

                                            $remaining = $get('remaining');

                                            if ($value > ($remaining + $value)) {
                                                $fail('Payment exceeds remaining amount');
                                            }
                                        };
                                    }),
                                Hidden::make('already_paid')->default(0),
                                TextInput::make('amount')
                                    ->label(__('messages.payment_amount'))
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                        $total = $get('total_amount') ?? 0;
                                        $alreadyPaid = $get('already_paid') ?? 0;

                                        $remaining = $total - ($alreadyPaid + $state);

                                        $set('remaining', $remaining);
                                    })
                                    ->maxValue(fn ($get) => $get('total_amount') - $get('already_paid')),
                                TextInput::make('remaining')
                                    ->label(__('messages.remaining_amount'))
                                    ->numeric()
                                    ->prefix('EGP')
                                    ->readOnly(),
                            ]),
                        Tab::make('Payment Details')
                        ->label(__('messages.payment_details'))
                        ->components([
                            Select::make('payment_method')
                                    ->label(__('messages.payment_method'))
                                    ->options(PaymentMethod::class)
                                    ->required(),
                            Select::make('status')
                                ->label(__('messages.payment_status'))
                                ->options(PaymentStatus::class)
                                ->default('pending')
                                ->required(),

                            DateTimePicker::make('paid_at')
                                ->label(__('messages.paid_at'))
                                ->required(),

                        ]),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
