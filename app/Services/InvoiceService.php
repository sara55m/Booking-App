<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Booking $booking,Payment $payment): string
    {
        //generate unique invoice number
        $invoiceNumber = 'INV-' . time();


        $booking->update([
            'invoice_number' => $invoiceNumber,
        ]);

        //generate invoice pdf
        $pdf = Pdf::loadView('invoices.invoice', [
            'booking' => $booking,
            'payment'=>$payment
        ]);

        $fileName = 'invoices/' . $invoiceNumber . '.pdf';

        //store invoice file path
        Storage::disk('public')->put(
            $fileName,
            $pdf->output()
        );

        $booking->update([
            'invoice_path' => $fileName,
        ]);

        return $fileName;
    }
}
