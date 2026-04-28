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
        $room = Room::find($data['room_id']);
        $roomId = $data['room_id'];

        if (!Booking::isRoomAvailable($roomId, $data['check_in'], $data['check_out'])) {
            Notification::make()
                ->title('Room is not available for selected dates.')
                ->danger()
                ->send();

            $this->halt();
        }

        $nights = Carbon::parse($data['check_in'])
            ->diffInDays(Carbon::parse($data['check_out']));

        $data['total_price'] = $room->{'price-per-night'} * $nights;

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
