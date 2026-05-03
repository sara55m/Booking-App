<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->sortable(),
                TextColumn::make('property.name')
                    ->label(__('messages.property'))
                    ->sortable(),
                TextColumn::make('room.name')
                    ->label(__('messages.room'))
                    ->sortable(),
                TextColumn::make('number_of_nights')
                    ->label(__('messages.number_of_nights'))
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(__('messages.total_price'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'checked_in',
                        'primary' => 'checked_out',
                        'danger' => 'cancelled',
                    ])
                    ->searchable(),
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
                SelectFilter::make('status')
                ->label(__('messages.status'))
                ->options([
                    'pending' => __('messages.pending'),
                    'confirmed' => __('messages.confirmed'),
                    'checked_in' => __('messages.checked_in'),
                    'checked_out' => __('messages.checked_out'),
                    'cancelled' => __('messages.cancelled'),
                ]),

                SelectFilter::make('property_id')
                    ->relationship('property', 'name')
                    ->label(__('messages.property'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('room_id')
                    ->relationship('room', 'name')
                    ->label(__('messages.room'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming_bookings')
                    ->label(__('messages.upcoming_bookings'))
                    ->query(fn ($query) =>
                        $query->whereDate('check_in','>=', today())),

                Filter::make('current_bookings')
                ->label(__('messages.current_bookings'))
                ->query(fn ($query) =>
                    $query->whereDate('check_in', '<=', today())
                            ->whereDate('check_out', '>=', today())
                ),
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
