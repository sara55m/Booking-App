<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Notifications\BookingExpiredNotification;
use App\Notifications\BookingExpiredAdminNotification;
use App\Enums\BookingCancellationReason;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class ProcessExpiredBookingsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Booking::where('status', BookingStatus::PENDING)
            ->where('payment_status',BookingPaymentStatus::UNPAID)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with('user')
            ->chunkById(100,function($bookings){
                $admins=User::where('role', 'admin')->get();
                
                foreach ($bookings as $booking) {

                    $booking->update([
                        'status' => BookingStatus::CANCELLED,
                        'cancellation_reason'=>BookingCancellationReason::PAYMENT_EXPIRED,
                    ]);

                    //send booking expiration notification to admin
                    Notification::send(
                        $admins,
                        new BookingExpiredAdminNotification($booking)
                    );

                    //send expiration mail
                    $booking->user->notify(
                        new BookingExpiredNotification($booking)
                    );
                }

            });


    }
}
