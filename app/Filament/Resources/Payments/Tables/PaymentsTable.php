<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\PaymentStatus;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Enums\PaymentMethod;
use Filament\Tables\Filters\SelectFilter;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_id')
                    ->label(__('messages.booking'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.payment_status'))
                    ->badge()
                    ->colors([
                        'warning' => PaymentStatus::PENDING->value,
                        'success' => PaymentStatus::PAID->value,
                        'danger' => PaymentStatus::FAILED->value,
                        'info' => PaymentStatus::REFUNDED->value,
                        'primary' => PaymentStatus::CANCELLED->value,
                        'partial' => PaymentStatus::PARTIAL->value,
                    ])
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('messages.payment_method'))
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label(__('messages.paid_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
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
                //amount range filter
                Filter::make('amount')
                ->label(__('messages.amount'))
                ->form([
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
                ->form([
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
