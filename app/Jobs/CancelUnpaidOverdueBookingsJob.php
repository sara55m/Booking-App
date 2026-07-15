<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Models\User;
use App\Enums\BookingStatus;
use App\Enums\BookingCancellationReason;
use App\Notifications\BookingAutoCancelledNotification;
use App\Notifications\BookingAutoCancelledAdminNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentStatus;

class CancelUnpaidOverdueBookingsJob implements ShouldQueue
{
    use Queueable;

    protected int $gracePeriodDays = 3;

    public function handle(): void
    {
        Booking::whereDate('balance_due_date', '<=', now())
            ->where('status', BookingStatus::CONFIRMED)
            ->with(['user', 'payments'])
            ->chunkById(100, function ($bookings) {
                $admins = User::where('role', 'admin')->get();

                //if the booking is already fully paid, skip it
                foreach ($bookings as $booking) {
                    if (! $booking->hasOutstandingBalance()) {
                        continue;
                    }

                    //if the booking is not paid and the balance due date is past the grace period, cancel it and mark payments as forfeited
                    DB::transaction(function () use ($booking) {
                        $booking->payments()
                            ->where('status', PaymentStatus::PAID)
                            ->update(['status' => PaymentStatus::FORFEITED]);

                        $booking->update([
                            'status' => BookingStatus::CANCELLED,
                            'cancellation_reason' => BookingCancellationReason::BALANCE_UNPAID,
                        ]);
                    });

                    $booking->user->notify(
                        new BookingAutoCancelledNotification($booking)
                    );

                    Notification::send(
                        $admins,
                        new BookingAutoCancelledAdminNotification($booking)
                    );
                }
            });
    }
}