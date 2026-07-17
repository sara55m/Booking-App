<?php

namespace App\Listeners;

use App\Events\BookingPaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\BookingConfirmedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingConfirmedAdminNotification;

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
            'property'
        ]);

        $user=$booking->user;

        $user->notify(
            new BookingConfirmedNotification($booking)
        );

        //send database notifications to all admins
        $admins=User::where('role','admin')->get();

        Notification::send(
            $admins,
            new BookingConfirmedAdminNotification($booking)
        );
    }
}
