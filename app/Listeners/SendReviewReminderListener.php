<?php

namespace App\Listeners;

use App\Events\BookingCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ReviewReminderNotification;

class SendReviewReminderListener implements ShouldQueue
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
    public function handle(BookingCompleted $event): void
    {
        $booking = $event->booking->loadMissing([
            'user',
            'property',
        ]);

        //send review reminder mail notification after an hour of the event firing
        $booking->user->notify(
        (new ReviewReminderNotification($booking))
        ->delay(now()->addHour()));
    }
}
