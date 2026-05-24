<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\BookingPaymentReminderNotification;
use App\Models\Booking;

class SendBookingPaymentReminderListener implements ShouldQueue
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
    public function handle(BookingCreated $event): void
    {
        $booking=$event->booking;

        // prevent sending if expired somehow
        if ($booking->expires_at->isPast()) {
            return;
        }

        $booking->user->notify(new BookingPaymentReminderNotification($booking));
    }
}
