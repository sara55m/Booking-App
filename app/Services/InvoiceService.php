<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generate(Booking $booking,Payment $payment): string
    {
        //generate unique invoice number
        $invoiceNumber = 'INV-' . time();


        $booking->update([
            'invoice_number' => $invoiceNumber,
        ]);
        //calculate the payment amount before applying the reward discount and calculate the total paid amount for the booking
        $paymentPortion = $payment->amount + $payment->discount_amount;

        $totalPaid = $booking->payments()
        ->where('status', \App\Enums\PaymentStatus::PAID)
        ->sum(DB::raw('amount + discount_amount'));

        $currentRewardBalance = $booking->user->fresh()->reward_points;

        //generate invoice pdf
        $pdf = Pdf::loadView('invoices.invoice', [
            'booking' => $booking,
            'payment'=>$payment,
            'portion'=>$paymentPortion,
            'totalPaid'=>$totalPaid,
            'currentRewardBalance'=>$currentRewardBalance
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
