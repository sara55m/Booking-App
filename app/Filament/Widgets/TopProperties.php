<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Property;
use Filament\Tables\Columns\TextColumn;

class TopProperties extends TableWidget
{
    public function heading(): string
    {
        return __('messages.top_rated_properties');
    }
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Property::query()->orderBy('average_rating', 'desc')->limit(5))
            ->columns([
                TextColumn::make('name')->label(__('messages.name'))->searchable()->sortable(),
                TextColumn::make('average_rating')->label(__('messages.average_rating'))->suffix(' ⭐'),
                TextColumn::make('reviews_count')->label(__('messages.reviews_count')),

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
