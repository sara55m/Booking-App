<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\PaymentSucceededNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PaymentCreatedAdminNotification;
use App\Enums\PaymentStatus;

class SendPaymentReceiptEmailListener implements ShouldQueue
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
    public function handle(PaymentSucceeded $event): void
    {
        $booking = $event->booking->loadMissing([
            'user',
            'property',
        ]);

        $payment = $event->payment;


        $user=$booking->user;

        //send database notifications to all admins
        $admins=User::where('role','admin')->get();

        Notification::send(
            $admins,
            new PaymentCreatedAdminNotification($booking,$payment)
        );

        // Don't send a payment receipt for the first payment.
        // The booking confirmation email already includes the invoice.
        if ($booking->payments()
        ->where('status', PaymentStatus::PAID)
        ->count() === 1) {
        return;
        }

        $user->notify(
            new PaymentSucceededNotification($booking,$payment)
        );
    }
}
