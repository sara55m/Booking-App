<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Enums\PaymentStatus;

class InvoiceService
{
    public function generate(Booking $booking,?Payment $payment=null): string
    {
        $booking->loadMissing([
            'property.policy',
            'user',
            'payments',
        ]);
        //generate unique invoice number
        $invoiceNumber = 'INV-' . time();


        $booking->update([
            'invoice_number' => $invoiceNumber,
        ]);

        //in refund case get the latest payment
        $currentPayment=$payment ?? $booking->payments()->latest('paid_at')->first();
        //calculate the payment amount before applying the reward discount and calculate the total paid amount for the booking
        $paymentPortion = $currentPayment
            ? $currentPayment->amount + $currentPayment->discount_amount
            : 0;

            $totalPaid = $booking->payments
            ->where('status', PaymentStatus::PAID)
            ->sum(fn ($payment) => $payment->amount + $payment->discount_amount);

        $totalEarnedPoints = $booking->payments
            ->sum('earned_points');

        $totalRedeemedPoints = $booking->payments
            ->sum('redeemed_points');

        $totalRewardDiscount = $booking->payments
            ->sum('discount_amount');

        $totalRefunded = $booking->payments
            ->where('status', PaymentStatus::REFUNDED)
            ->sum(fn ($payment) => $payment->amount + $payment->discount_amount);

        $currentRewardBalance = $booking->user->fresh()->reward_points;

        //check_in/out times
        $checkInFrom = Carbon::parse($booking->property->policy->check_in_from)->format('h:i A');

        $checkInUntil =Carbon::parse($booking->property->policy->check_in_until)->format('h:i A');

        $checkOutFrom = Carbon::parse($booking->property->policy->check_out_from)->format('h:i A');

        $checkOutUntil = Carbon::parse($booking->property->policy->check_out_until)->format('h:i A');

        //generate invoice pdf
        $pdf = Pdf::loadView('invoices.invoice', [
            'booking' => $booking,
            'payment'=>$currentPayment,
            'portion'=>$paymentPortion,
            'totalPaid'=>$totalPaid,
            'totalRefunded' => $totalRefunded,
            'totalEarnedPoints' => $totalEarnedPoints,
            'totalRedeemedPoints' => $totalRedeemedPoints,
            'totalRewardDiscount' => $totalRewardDiscount,
            'currentRewardBalance'=>$currentRewardBalance,
            'checkInFrom' => $checkInFrom,
            'checkInUntil' => $checkInUntil,
            'checkOutFrom' => $checkOutFrom,
            'checkOutUntil' => $checkOutUntil,
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
