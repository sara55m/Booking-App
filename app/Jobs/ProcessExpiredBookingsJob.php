<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Notifications\BookingExpiredNotification;

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
            ->chunkById(100,function($bookings){
                foreach ($bookings as $booking) {

                    $booking->update([
                        'status' => BookingStatus::CANCELLED
                    ]);

                    //send expiration mail
                    $booking->user->notify(
                        new BookingExpiredNotification($booking)
                    );
                }

            });


    }
}
