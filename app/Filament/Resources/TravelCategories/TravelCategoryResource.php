<?php

namespace App\Filament\Resources\TravelCategories;

use App\Filament\Resources\TravelCategories\Pages\CreateTravelCategory;
use App\Filament\Resources\TravelCategories\Pages\ViewTravelCategory;
use App\Filament\Resources\TravelCategories\Pages\EditTravelCategory;
use App\Filament\Resources\TravelCategories\Pages\ListTravelCategories;
use App\Filament\Resources\TravelCategories\Schemas\TravelCategoryForm;
use App\Filament\Resources\TravelCategories\Tables\TravelCategoriesTable;
use App\Models\TravelCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TravelCategoryResource extends Resource
{
    protected static ?string $model = TravelCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sun';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.travel_categories');
    }
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('messages.travel_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.travel_categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.travel_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return TravelCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTravelCategories::route('/'),
            'create' => CreateTravelCategory::route('/create'),
            'View' => ViewTravelCategory::route('/{record}/view'),
            'edit' => EditTravelCategory::route('/{record}/edit'),
        ];
    }
}
