<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Booking;
use App\Models\User;
use App\Enums\BookingStatus;
use App\Notifications\BookingBalanceDueReminderNotification;
use App\Notifications\BookingBalanceOverdueAdminNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingBalanceOverdueNotification;

class CheckBookingBalanceDueJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $today = today();

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        //send notification to admins for bookings with balance due in the past
        Booking::whereDate('balance_due_date', '<', $today->toDateString())
            ->where('status', BookingStatus::CONFIRMED)
            ->whereNull('balance_overdue_notified_at')
            ->with(['payments', 'user', 'property'])
            ->chunkById(100, function ($bookings) use ($admins){
                foreach ($bookings as $booking) {
                    if ($booking->hasOutstandingBalance()) {
                        //send mail to user to complete payment within the grace period(24 hours)
                        $booking->user->notify(
                            new BookingBalanceOverdueNotification($booking)
                        );
                        Notification::send(
                            $admins,
                            new BookingBalanceOverdueAdminNotification($booking)
                        );
                        //update balance due notified at column
                        $booking->forceFill(['balance_overdue_notified_at' => now()])->save();
                    }
                }
            });
        //send reminder to users with balance due in 2 days
        $reminderThreshold = $today->copy()->addDays(2)->toDateString();

        Booking::where('balance_due_date', $reminderThreshold)
            ->where('status', BookingStatus::CONFIRMED)
            ->whereNull('balance_due_reminder_queued_at')
            ->whereNull('balance_due_reminder_sent_at')
            ->with(['user', 'payments'])
            ->chunkById(100, function ($bookings) {
                foreach ($bookings as $booking) {
                    if (! $booking->hasOutstandingBalance()) {
                        continue;
                    }

                    $booking->user->notify(
                        new BookingBalanceDueReminderNotification($booking)
                    );

                    $booking->update([
                        'balance_due_reminder_queued_at' => now(),
                    ]);
                }
            });
    }
}
