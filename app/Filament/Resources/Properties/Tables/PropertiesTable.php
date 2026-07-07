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
use Filament\Actions\Action;
use App\Models\Property;
use App\Filament\Resources\Reviews\ReviewResource;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label(__('messages.city'))
                    ->searchable(),
                TextColumn::make('propertyType.name')
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
                TextColumn::make('average_rating')
                    ->label(__('messages.average_rating'))
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' ⭐')
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 2 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('favorited_by_count')
                    ->label(__('messages.favorited_by_count'))
                    ->counts('favoritedBy')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('city')
                ->label(__('messages.city'))
                ->relationship('city', 'name'),
                SelectFilter::make('PropertyType')
                ->relationship('PropertyType', 'name')
                ->label(__('messages.type')),
                SelectFilter::make('is_active')
                ->label(__('messages.is_active'))
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                    SelectFilter::make('favorite_user')
                    ->label(__('messages.favorited_by'))
                    ->relationship('favoritedBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('view_reviews')
                    ->label(__('messages.view_reviews'))
                    ->icon('heroicon-o-chat-bubble-oval-left')
                    ->url(fn (Property $record) => ReviewResource::getUrl('index', [
                        'filters' => [
                            'property_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
