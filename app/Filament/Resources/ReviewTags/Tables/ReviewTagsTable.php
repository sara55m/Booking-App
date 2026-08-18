<?php

namespace App\Filament\Resources\ReviewTags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ReviewTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__("messages.name"))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__("messages.type"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'positive' => 'success',
                        'negative' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__("messages.is_active"))
                    ->boolean(),
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
                SelectFilter::make('type')
                ->label(__("messages.type"))
                ->options([
                    'positive'=>__("messages.positive"),
                    'negative'=>__("messages.negative"),
                    'neutral'=>__("messages.neutral")
                ]),

                TernaryFilter::make('is_active')
                    ->label(__("messages.is_active")),
            ])
            ->recordActions([
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
