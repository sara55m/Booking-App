<?php

namespace App\Filament\Resources\ReviewTags\Pages;

use App\Filament\Resources\ReviewTags\ReviewTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReviewTag extends EditRecord
{
    protected static string $resource = ReviewTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
