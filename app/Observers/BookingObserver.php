<?php

namespace App\Observers;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Events\BookingCompleted;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        //generate reference and update without firing updated event
        $booking->updateQuietly([
            'reference'=>'BK'.str_pad($booking->id,6,'0',STR_PAD_LEFT)
        ]);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if (
            $booking->wasChanged('status') &&
            $booking->status === BookingStatus::COMPLETED
        ) {
            event(new BookingCompleted($booking));
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}
