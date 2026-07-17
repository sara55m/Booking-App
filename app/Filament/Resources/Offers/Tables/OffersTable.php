<?php

namespace App\Filament\Resources\Offers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\TextInput;
use App\Enums\OfferStatus;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->label(__("messages.title"))
                    ->sortable(),
                TextColumn::make('property.name')
                    ->label(__("messages.property"))
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__("messages.code"))
                    ->searchable(),
                TextColumn::make('discount_type')
                    ->colors([
                        'success' => 'percentage',
                        'primary' => 'fixed',
                    ])
                    ->label(__("messages.discount_type")),
                TextColumn::make('discount_value')
                    ->numeric()
                    ->sortable()
                    ->label(__("messages.discount_value")),
                IconColumn::make('is_active')
                    ->label(__("messages.is_active"))
                    ->boolean(),
                    //dynamic state offer status
                TextColumn::make('computed_status')
                ->label(__("messages.status"))
                ->badge()
                ->formatStateUsing(fn (OfferStatus $state) => $state->label())
                ->color(fn (OfferStatus $state) => $state->color()),
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
                //property filter
                SelectFilter::make('property_id')
                ->relationship('property', 'name')
                ->label(__('messages.property'))
                ->searchable()
                ->preload(),

                //discount type filter
                SelectFilter::make('discount_type')
                    ->label(__('messages.discount_type'))
                    ->options([
                        'fixed' => __('messages.fixed'),
                        'percentage' => __('messages.percentage'),
                    ]),

                    //discount value filter
                Filter::make('discount_value')
                ->label(__("messages.discount_value"))
                ->schema([

                    TextInput::make('min_discount')
                        ->numeric()
                        ->minValue(0.01)
                        ->label(__('messages.minimum_discount')),

                    TextInput::make('max_discount')
                        ->numeric()
                        ->minValue(0.01)
                        ->label(__('messages.maximum_discount')),
                ])
                ->query(function ($query, array $data) {

                    return $query
                        ->when(
                            $data['min_discount'] ?? null,
                            fn ($query, $value) =>
                                $query->where(
                                    'discount_value',
                                    '>=',
                                    $value
                                )
                        )
                        ->when(
                            $data['max_discount'] ?? null,
                            fn ($query, $value) =>
                                $query->where(
                                    'discount_value',
                                    '<=',
                                    $value
                                )
                        );
                }),

                TernaryFilter::make('is_active')
                    ->label(__('messages.is_active')),
                    
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
