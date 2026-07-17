<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Filament\Resources\Bookings\BookingResource;

class RoomAvailabilityCalendar extends FullCalendarWidget
{
    public function config(): array
    {
        return [
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek',
            ],
        ];
    }
    protected function headerActions(): array
    {
        return [];
    }
    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.rooms.*');
    }
    
    protected static ?string $heading = null;

    public function mount(): void
    {
        static::$heading = __('messages.room_availability_calendar');
    }

    //each booking will be represented as an event in the calendar, clicking on it will redirect to the booking view page
    public function onEventClick(array $event): void
    {
        $this->redirect(
            BookingResource::getUrl('view', [
                'record' => $event['id'],
            ])
        );
    }

    public ?int $roomId = null;

    public function fetchEvents(array $fetchInfo): array
    {

        if (!$this->roomId) {
            return [];
        }

        $events = [];

        $start = $fetchInfo['start'];
        $end   = $fetchInfo['end'];

        //get all room bookings that overlap with the calendar range and are not cancelled
        $bookings = Booking::where('room_id', $this->roomId)
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->where('check_in', '<', $end)
            ->where('check_out', '>', $start)
            ->orderBy('check_in')
            ->get();

        $currentDate = $start;

        foreach ($bookings as $booking) {

            //Add AVAILABLE gap before booking
            if ($currentDate < $booking->check_in) {
                $events[] = [
                    'id' => $booking->id,
                    'title' => 'Available',
                    'start' => $currentDate,
                    'end' => $booking->check_in,
                    'backgroundColor' => '#22c55e', // green
                    'borderColor' => '#22c55e',
                ];
            }

            //Add booked events
            $events[]=[
                'id' => $booking->id,
                'title' => 'Booked By '.$booking->user->name . ' | ' . $booking->check_in,
                'start' => $booking->check_in,
                'end' => $booking->check_out,
               'backgroundColor' => match ($booking->status) {
                    BookingStatus::PENDING => '#f59e0b',
                    BookingStatus::CONFIRMED => '#ef4444',
                    BookingStatus::CHECKED_IN => '#10b981',
                    BookingStatus::CHECKED_OUT => '#3b82f6',
                    BookingStatus::COMPLETED => '#6b7280',
                    default => '#6b7280',
                },
                'borderColor' => '#ef4444',
            ];

            //Move current date pointer
            $currentDate=$booking->check_out;
        }
        //Add remaining AVAILABLE after last booking
        if ($currentDate < $end) {
            $events[] = [
                'title' => 'Available',
                'start' => $currentDate,
                'end' => $end,
                'backgroundColor' => '#22c55e',
                'borderColor' => '#22c55e',
            ];
        }

        return $events;

    }
}
