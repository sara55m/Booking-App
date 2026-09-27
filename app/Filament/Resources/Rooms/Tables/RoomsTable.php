<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name')
                    ->label(__('messages.property'))
                    ->sortable(),
                TextColumn::make('roomType.name')
                    ->label(__('messages.room_type'))
                    ->searchable(),
                TextColumn::make('number')
                    ->label(__('messages.room_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roomType.base_price')
                    ->label(__('messages.price_per_night'))
                    ->money(strtoupper(config('app.currency', 'USD')))
                    ->sortable(),
                TextColumn::make('roomType.capacity')
                    ->label(__('messages.capacity'))
                    ->numeric()
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
                SelectFilter::make('property_id')
                    ->label(__('messages.property'))
                    ->relationship('property', 'name'),

                SelectFilter::make('room_type_id')
                ->label(__('messages.room_type'))
                ->relationship('roomType', 'name'),

                SelectFilter::make('roomType.capacity')
                ->label(__('messages.capacity'))
                ->options([
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5+',
                ]),

                Filter::make('roomType.base_price')
                    ->label(__('messages.price_range'))
                    ->schema([
                        TextInput::make('min_price')->numeric()->label(__('messages.min_price')),
                        TextInput::make('max_price')->numeric()->label(__('messages.max_price')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_price'], fn ($q, $value) => $q->where('base_price', '>=', $value))
                            ->when($data['max_price'], fn ($q, $value) => $q->where('base_price', '<=', $value));
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
