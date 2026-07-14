<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Services\InvoiceService;

class GenerateInvoiceAfterCancellationListener
{
    /**
     * Create the event listener.
     */
    public function __construct(protected InvoiceService $invoiceService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        $booking->load('payments');
        $this->invoiceService->generate(
            $booking,
        );
    }
}
