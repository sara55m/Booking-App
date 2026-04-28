<?php

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('messages.city'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('rating')
                ->label(__('messages.rating'))
                ->formatStateUsing(fn ($state) => '⭐ ' . $state),
                IconColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('city')
                ->label(__('messages.city')),
                SelectFilter::make('type')
                ->label(__('messages.type')),
                SelectFilter::make('is_active')
                ->label(__('messages.is_active'))
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
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
