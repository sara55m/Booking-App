<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\DeleteAction;

class ViewRoom extends ViewRecord
{
    protected static string $resource = RoomResource::class;


    protected function getHeaderWidgets(): array
    {
        return [
            //pass the cuurent room id to the widget
            \App\Filament\Widgets\RoomAvailabilityCalendar::make([
                'roomId' => $this->record->id,
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
