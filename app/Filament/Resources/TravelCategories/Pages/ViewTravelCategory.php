<?php

namespace App\Filament\Resources\TravelCategories\Pages;

use App\Filament\Resources\TravelCategories\TravelCategoryResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelCategory extends ViewRecord
{
    protected static string $resource = TravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
