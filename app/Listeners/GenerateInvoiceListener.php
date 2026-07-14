<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

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
    public function handle(PaymentSucceeded $event): void
    {
        $this->invoiceService->generate(
            $event->booking,
            $event->payment
        );

    }
}
