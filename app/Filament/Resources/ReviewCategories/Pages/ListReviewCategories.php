<?php

namespace App\Filament\Resources\ReviewCategories\Pages;

use App\Filament\Resources\ReviewCategories\ReviewCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReviewCategories extends ListRecords
{
    protected static string $resource = ReviewCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
