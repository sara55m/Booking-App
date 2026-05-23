<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Notifications\BookingExpiredNotification;

class ExpireBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredBookings = Booking::where('status', BookingStatus::PENDING)
            ->where('payment_status',BookingPaymentStatus::UNPAID)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredBookings as $booking) {

            //send expiration mail
            $booking->user->notify(
                new BookingExpiredNotification($booking)
            );

            $booking->update([
                'status' => BookingStatus::CANCELLED
            ]);

            $this->info("Booking {$booking->id} cancelled");
        }

        return Command::SUCCESS;
    }
}
