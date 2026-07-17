<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Events\BookingCompleted;

class MarkCompletedBookingsJob implements ShouldQueue
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
        Booking::query()
            ->where('status', BookingStatus::CHECKED_OUT)
            ->whereDate('check_out', '<=', today())
            ->chunkById(100, function ($bookings) {

                foreach ($bookings as $booking) {

                    $booking->update([
                        'status' => BookingStatus::COMPLETED,
                    ]);

                    //fire booking completed event--->send review reminder
                    event(new BookingCompleted($booking));
                }
            });
    }
}
