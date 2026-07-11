<?php

namespace App\Filament\Resources\PropertyTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;


class PropertyTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->boolean(),
                    TextColumn::make('properties_count')
                    ->label(__('messages.properties_count'))
                    ->counts('properties')
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
                SelectFilter::make('is_active')
                    ->label(__('messages.is_active'))
                    ->options([
                        1 => __('messages.active'),
                        0 => __('messages.inactive'),
                    ]),
                Filter::make('has_properties')
                    ->label(__('messages.has_properties'))
                    ->query(fn ($query) => $query->has('properties')),

                Filter::make('unused')
                    ->label(__('messages.unused'))
                    ->query(fn ($query) => $query->doesntHave('properties')),
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
