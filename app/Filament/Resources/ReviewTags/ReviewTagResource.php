<?php

namespace App\Filament\Resources\ReviewTags;

use App\Filament\Resources\ReviewTags\Pages\CreateReviewTag;
use App\Filament\Resources\ReviewTags\Pages\EditReviewTag;
use App\Filament\Resources\ReviewTags\Pages\ListReviewTags;
use App\Filament\Resources\ReviewTags\Schemas\ReviewTagForm;
use App\Filament\Resources\ReviewTags\Tables\ReviewTagsTable;
use App\Models\ReviewTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReviewTagResource extends Resource
{
    protected static ?string $model = ReviewTag::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.review_tags');
    }
    protected static ?int $navigationSort = 13;

    public static function getModelLabel(): string
    {
        return __('messages.review_tag');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.review_tags');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.review_tags');
    }

    public static function form(Schema $schema): Schema
    {
        return ReviewTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewTagsTable::configure($table);
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
            'index' => ListReviewTags::route('/'),
            'create' => CreateReviewTag::route('/create'),
            'edit' => EditReviewTag::route('/{record}/edit'),
        ];
    }
}
