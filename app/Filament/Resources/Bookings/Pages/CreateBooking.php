<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Room;
use Carbon\Carbon;
use App\Models\Booking;
use Filament\Notifications\Notification;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
}
