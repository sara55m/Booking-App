<?php

namespace App\Listeners;

use App\Events\BookingPaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\BookingConfirmedNotification;


class SendBookingConfirmedMailListener implements ShouldQueue
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
    public function handle(BookingPaymentConfirmed $event): void
    {
        $booking = $event->booking->fresh([
            'user',
            'property',
        ]);

        $user=$booking->user;

        $user->notify(
            new BookingConfirmedNotification($booking)
        );
    }
}
