<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Country;
use App\Filament\Resources\Cities\CityResource;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('iso_code')
                    ->label(__('messages.iso_code'))
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('flag')
                    ->label(__("messages.flag"))
                    ->disk('public')
                    ->imageHeight(45)
                    ->imageWidth(60)
                    ->getStateUsing(fn ($record) => Storage::disk('public')->url($record->flag)),
                TextColumn::make('currency')
                    ->label(__('messages.currency'))
                    ->searchable(),
                    TextColumn::make('cities_count')
                    ->label(__('messages.cities_count'))
                    ->counts('cities')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->boolean(),
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
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('view_cities')
                    ->label(__('messages.view_cities'))
                    ->icon('heroicon-o-map')
                    ->visible(fn (Country $record) => $record->cities()->exists())
                    ->url(fn (Country $record) => CityResource::getUrl('index', [
                        'filters' => [
                            'country_id' => [
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
