<?php

namespace App\Filament\Resources\RoomTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use App\Filament\Resources\Rooms\RoomResource;
use App\Models\RoomType;

class RoomTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name')
                    ->label(__("messages.property"))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__("messages.name"))
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label(__("messages.capacity"))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_price')
                    ->label(__("messages.price_per_night"))
                    ->money()
                    ->sortable(),
                TextColumn::make('rooms_count')
                    ->label(__("messages.rooms_count"))
                    ->counts('rooms'),
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
                //property filter
                SelectFilter::make('property_id')
                    ->label(__('messages.property'))
                    ->relationship('property', 'name'),
                //filter by capacity
                SelectFilter::make('capacity')
                    ->label(__('messages.capacity'))
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5+',
                    ]),

                //filter by price range
                Filter::make('base_price')
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
                //show rooms action
                Action::make('view_rooms')
                    ->label(__('messages.view_rooms'))
                    ->icon('heroicon-o-key')
                    ->visible(fn (RoomType $record) => $record->rooms()->exists())
                    ->url(fn (RoomType $record) => RoomResource::getUrl('index', [
                        'filters' => [
                            'room_type_id' => [
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
