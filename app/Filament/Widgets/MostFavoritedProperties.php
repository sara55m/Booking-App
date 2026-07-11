<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Property;
use Filament\Tables\Columns\TextColumn;

class MostFavoritedProperties extends TableWidget
{
    protected static ?string $heading = null;

    public function mount(): void
    {
        static::$heading = __('messages.most_favorited_properties');
    }
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 7;
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder =>
            Property::query()
            ->withCount('favoritedBy')
            ->orderByDesc('favorited_by_count')
            ->limit(5))
            ->columns([
                TextColumn::make('name')
                ->label(__('messages.name'))
                ->searchable(),

                TextColumn::make('city.name')
                ->label(__('messages.city'))
                ->searchable(),

                TextColumn::make('propertyType.name')
                ->label(__('messages.property_type'))
                ->searchable(),

            TextColumn::make('favorited_by_count')
                ->label(__('messages.favorited_by_count'))
                ->badge()
                ->color('success')
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateHeading(__('messages.no_properties'));
    }
}
