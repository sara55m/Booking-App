<?php

namespace App\Filament\Resources\TravelCategories\Pages;

use App\Filament\Resources\TravelCategories\TravelCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelCategory extends EditRecord
{
    protected static string $resource = TravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
