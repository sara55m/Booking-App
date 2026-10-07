<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Notifications\ArrivalReminderNotification;
use Illuminate\Notifications\Events\NotificationSent;

class MarkArrivalReminderAsSent
{
    public function handle(NotificationSent $event): void
    {
        if (
            $event->channel !== 'mail'
            || ! $event->notification instanceof ArrivalReminderNotification
        ) {
            return;
        }

        Booking::whereKey($event->notification->getBooking()->getKey())
            ->whereNull('arrival_reminder_sent_at')
            ->update([
                'arrival_reminder_sent_at' => now(),
            ]);
    }
}
