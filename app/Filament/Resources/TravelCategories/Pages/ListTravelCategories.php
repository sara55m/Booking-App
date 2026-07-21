<?php

namespace App\Filament\Resources\TravelCategories\Pages;

use App\Filament\Resources\TravelCategories\TravelCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelCategories extends ListRecords
{
    protected static string $resource = TravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
