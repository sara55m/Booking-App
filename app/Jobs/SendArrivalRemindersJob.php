<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Events\BookingArrivalReminder;
use Illuminate\Support\Facades\DB;

class SendArrivalRemindersJob implements ShouldQueue
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
        Booking::readyForArrivalReminder()
            ->chunkById(100, function ($bookings) {

                foreach ($bookings as $booking) {

                    DB::transaction(function () use ($booking) {
                        $booking->update([
                            'arrival_reminder_sent_at' => now(),
                        ]);

                        DB::afterCommit(function () use ($booking) {
                            BookingArrivalReminder::dispatch($booking);
                        });
                });
                }
            });
    }
}
