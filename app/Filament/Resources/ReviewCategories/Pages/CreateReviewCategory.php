<?php

namespace App\Filament\Resources\ReviewCategories\Pages;

use App\Filament\Resources\ReviewCategories\ReviewCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReviewCategory extends CreateRecord
{
    protected static string $resource = ReviewCategoryResource::class;
}
