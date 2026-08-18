<?php

namespace App\Filament\Resources\ReviewCategories\Pages;

use App\Filament\Resources\ReviewCategories\ReviewCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReviewCategory extends EditRecord
{
    protected static string $resource = ReviewCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
