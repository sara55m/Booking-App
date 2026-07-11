<?php

namespace App\Filament\Resources\RewardPoints\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\RewardPointType;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class RewardPointsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                ->label(__("messages.id"))
                ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->formatStateUsing(fn (RewardPointType $state) => match ($state) {
                        RewardPointType::EARNED => __('messages.earned'),
                        RewardPointType::REDEEMED => __('messages.redeemed'),
                        RewardPointType::RETURNED => __('messages.returned'),
                        RewardPointType::REVERSED => __('messages.reversed'),
                    })
                    ->color(fn (RewardPointType $state) => $state->color())
                    ->sortable(),

                TextColumn::make('points')
                    ->label(__("messages.reward_points_number"))
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => match ($record->type) {
                        RewardPointType::EARNED,
                        RewardPointType::RETURNED => "+{$state}",
                
                        RewardPointType::REDEEMED,
                        RewardPointType::REVERSED => "-{$state}",
                    })
                    ->color(fn ($record) => match ($record->type) {
                        RewardPointType::EARNED,
                        RewardPointType::RETURNED => 'success',
                
                        RewardPointType::REDEEMED,
                        RewardPointType::REVERSED => 'danger',
                    }),

                TextColumn::make('payment.booking.reference')
                    ->label(__("messages.booking"))
                    ->sortable(),

                TextColumn::make('payment.amount')
                    ->label(__('messages.payment_amount'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__("messages.description"))
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('created_at')
                    ->label(__("messages.created_at"))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                ->label(__("messages.type"))
                ->options([
                    RewardPointType::EARNED->value => __('messages.earned'),
                    RewardPointType::REDEEMED->value => __('messages.redeemed'),
                    RewardPointType::RETURNED->value => __('messages.returned'),
                    RewardPointType::REVERSED->value => __('messages.reversed'),
                ]),

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->label(__('messages.user')),

                Filter::make('created_at')
                ->schema([
                    DatePicker::make('from')->label(__("messages.from")),
                    DatePicker::make('until')->label(__("messages.until")),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
            ])
            ->recordActions([
            ])
            ->toolbarActions([

            ]);

    }
}
