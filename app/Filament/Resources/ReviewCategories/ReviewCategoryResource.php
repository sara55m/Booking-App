<?php

namespace App\Filament\Resources\ReviewCategories;

use App\Filament\Resources\ReviewCategories\Pages\CreateReviewCategory;
use App\Filament\Resources\ReviewCategories\Pages\EditReviewCategory;
use App\Filament\Resources\ReviewCategories\Pages\ListReviewCategories;
use App\Filament\Resources\ReviewCategories\Schemas\ReviewCategoryForm;
use App\Filament\Resources\ReviewCategories\Tables\ReviewCategoriesTable;
use App\Models\ReviewCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReviewCategoryResource extends Resource
{
    protected static ?string $model = ReviewCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.review_categories');
    }
    protected static ?int $navigationSort = 12;

    public static function getModelLabel(): string
    {
        return __('messages.review_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.review_categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.review_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return ReviewCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewCategoriesTable::configure($table);
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
            'index' => ListReviewCategories::route('/'),
            'create' => CreateReviewCategory::route('/create'),
            'edit' => EditReviewCategory::route('/{record}/edit'),
        ];
    }
}
