<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\BookingCancelledNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingCancelledAdminNotification;

class SendBookingCancelledEmailListener implements ShouldQueue
{

    use InteractsWithQueue;
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
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking->fresh([
            'user',
            'property'
        ]);

        $event->booking->user->notify(
            new BookingCancelledNotification($booking)
        );

        //send database notifications to all admins
        $admins=User::where('role','admin')->get();

        Notification::send(
            $admins,
            new BookingCancelledAdminNotification($booking)
        );
    }
}
