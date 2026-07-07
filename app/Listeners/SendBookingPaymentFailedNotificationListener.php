<?php

namespace App\Listeners;

use App\Events\BookingPaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingPaymentFailedAdminNotification;

class SendBookingPaymentFailedNotificationListener implements ShouldQueue
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
    public function handle(BookingPaymentFailed $event): void
    {
        $admins=User::where('role','admin')->get();

        Notification::send(
            $admins,
            new BookingPaymentFailedAdminNotification(
                $event->booking,
                $event->payment,)
        );
    }
}
