<?php

namespace App\Listeners;

use App\Events\BookingPaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\InvoiceService;

class GenerateInvoiceListener
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
    public function handle(BookingPaymentConfirmed $event): void
    {
        $this->invoiceService->generate(
            $event->booking,
            $event->payment
        );

    }
}
