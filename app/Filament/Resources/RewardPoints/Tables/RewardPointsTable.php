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
                ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__("messages.type"))
                    ->colors([
                        'success' =>RewardPointType::EARNED,
                        'danger' => RewardPointType::REDEEMED,
                    ])
                    ->badge()
                    ->sortable(),

                TextColumn::make('points')
                    ->label(__("messages.points"))
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->type === RewardPointType::EARNED
                            ? 'success'
                            : 'danger'
                    ),

                TextColumn::make('payment.booking.id')
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

                TextColumn::make('user.reward_points')
                    ->label(__('messages.current_balance'))
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label(__("messages.created_at"))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                ->label(__("messages.type"))
                ->options([
                    'earned'=> __("messages.earned"),
                    'redeemed'=> __("messages.redeemed")
                ]),

                Filter::make('created_at')
                ->form([
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
