<?php

namespace App\Listeners;

use App\Events\BookingArrivalReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ArrivalReminderNotification;

class SendArrivalReminderListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingArrivalReminder $event): void
    {
        $booking = $event->booking->loadMissing([
            'user',
            'property',
            'room.roomType',
        ]);

        $booking->user->notify(
            new ArrivalReminderNotification($booking)
        );
    }
}
