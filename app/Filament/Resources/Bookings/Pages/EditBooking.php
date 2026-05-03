<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\Room;
use Carbon\Carbon;
use App\Models\Booking;
use Filament\Notifications\Notification;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $recordId = $record->id;

        $roomId = $data['room_id'];

        $roomChanged = $data['room_id'] != $record->room_id;
        $checkInChanged = $data['check_in'] != $record->check_in;
        $checkOutChanged = $data['check_out'] != $record->check_out;

        // Only validate if something actually changed
        if ($roomChanged || $checkInChanged || $checkOutChanged) {

            if (!Booking::isRoomAvailable(
                $roomId,
                $data['check_in'],
                $data['check_out'],
                $recordId //exclude current booking
            )) {
                Notification::make()
                    ->title('Room is not available for selected dates.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
