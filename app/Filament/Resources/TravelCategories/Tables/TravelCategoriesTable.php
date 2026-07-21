<?php

namespace App\Filament\Resources\TravelCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class TravelCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__("messages.name"))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__("messages.slug"))
                    ->searchable(),
                IconColumn::make('icon')
                    ->label(__("messages.icon"))
                    ->icon(fn ($record) => $record->icon),
                IconColumn::make('is_active')
                    ->label(__("messages.is_active"))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__("messages.sort_order"))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__("messages.created_at"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__("messages.updated_at"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('messages.is_active'))
                    ->options([
                        1 => __('messages.active'),
                        0 => __('messages.inactive'),
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
