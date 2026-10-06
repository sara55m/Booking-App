<?php

namespace App\Listeners;

use App\Notifications\BookingBalanceDueReminderNotification;
use Illuminate\Notifications\Events\NotificationSent;

class MarkBookingBalanceReminderAsSent
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
    public function handle(NotificationSent $event): void
    {
        if (
            $event->channel !== 'mail'
            || ! $event->notification instanceof BookingBalanceDueReminderNotification
        ) {
            return;
        }

        $event->notification->getBooking()->update([
            'balance_due_reminder_sent_at' => now(),
        ]);
    }
}
