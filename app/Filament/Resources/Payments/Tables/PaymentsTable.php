<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\PaymentStatus;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Enums\PaymentMethod;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Payment;
use App\Filament\Resources\Bookings\BookingResource;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking.reference')
                    ->label(__('messages.booking_reference'))
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.payment_status'))
                    ->badge()
                    ->color(fn (PaymentStatus $state) => $state->color())
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('messages.payment_method'))
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label(__('messages.paid_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //booking reference filter
                SelectFilter::make('booking')
                ->label(__("messages.booking_reference"))
                ->relationship('booking','reference'),
                //amount range filter
                Filter::make('amount')
                ->label(__('messages.amount'))
                ->schema([
                    TextInput::make('min_amount')->numeric()->label(__('messages.min_amount')),
                    TextInput::make('max_amount')->numeric()->label(__('messages.max_amount')),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['min_amount'], fn ($q, $value) => $q->where('amount', '>=', $value))
                        ->when($data['max_amount'], fn ($q, $value) => $q->where('amount', '<=', $value));
                }),

                //payment status filter
                SelectFilter::make('status')
                ->label(__('messages.payment_status'))
                ->options(PaymentStatus::class),

                //payment method filter
                SelectFilter::make('payment_method')
                ->label(__('messages.payment_method'))
                ->options(PaymentMethod::class),

                //payment date range filter
                Filter::make('paid_at')
                ->label(__('messages.paid_at'))
                ->schema([
                    DatePicker::make('min_paid_at')->label(__('messages.min_paid_at')),
                    DatePicker::make('max_paid_at')->label(__('messages.max_paid_at')),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['min_paid_at'], fn ($q, $value) => $q->where('paid_at', '>=', $value))
                        ->when($data['max_paid_at'], fn ($q, $value) => $q->where('paid_at', '<=', $value));
                }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('view_booking')
                    ->label(__('messages.view_booking'))
                    ->icon('heroicon-o-calendar')
                    ->url(fn (Payment $record) => BookingResource::getUrl('view', [
                        'record' => $record->booking,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
