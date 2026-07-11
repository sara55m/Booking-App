<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\Booking;
use Filament\Notifications\Notification;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $recordId = $record->id;

        $roomId = $record->room_id;

        $checkInChanged = $data['check_in'] != $record->check_in;
        $checkOutChanged = $data['check_out'] != $record->check_out;

        // Only validate if something actually changed
        if ($checkInChanged || $checkOutChanged) {

            if (!Booking::isRoomAvailable(
                $roomId,
                $data['check_in'],
                $data['check_out'],
                $recordId //exclude current booking
            )) {
                Notification::make()
                    ->title(__('messages.room_not_available_in_these_dates'))
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
        //check the new status is valid
        $newStatus = $data['status'];
        if(!$record->canTransitionTo($newStatus)) {
            Notification::make()
                ->title(__('messages.invalid_status_transition', [
                    'from' => $record->status->value,
                    'to' => $newStatus,
                ]))
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
