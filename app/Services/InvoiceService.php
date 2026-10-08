<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Booking $booking, ?Payment $payment = null): string
    {
        $booking->loadMissing([
            'property.policy',
            'user',
            'payments',
        ]);

        // Generate invoice number.
        $invoiceNumber = 'INV-' . time();

        $booking->update([
            'invoice_number' => $invoiceNumber,
        ]);

        $currencyService = app(CurrencyService::class);
        $baseCurrency = strtoupper(config('app.currency', 'USD'));

        // For a payment invoice, use the currency entered for that payment.
        // Otherwise, use the user's preferred currency.
        $currentPayment = $payment
            ?? $booking->payments()->latest('paid_at')->first();

        $invoiceCurrency = strtoupper(
            $currentPayment?->requested_currency
                ?? $booking->user->currency
                ?? $baseCurrency
        );

        // Payment credit includes the reward discount applied to the booking.
        $paymentPortion = $currentPayment
            ? (float) $currentPayment->amount
                + (float) $currentPayment->discount_amount
            : 0.0;

        $paidPayments = $booking->payments
            ->where('status', PaymentStatus::PAID);

        $totalPaid = $paidPayments->sum(
            fn (Payment $paidPayment) =>
                (float) $paidPayment->amount
                + (float) $paidPayment->discount_amount
        );

        $totalEarnedPoints = $booking->payments->sum('earned_points');
        $totalRedeemedPoints = $booking->payments->sum('redeemed_points');
        $totalRewardDiscount = $booking->payments->sum('discount_amount');

        $refundedPayments = $booking->payments
            ->where('status', PaymentStatus::REFUNDED);

        $totalRefunded = $refundedPayments->sum(
            fn (Payment $refundedPayment) =>
                (float) $refundedPayment->amount
                + (float) $refundedPayment->discount_amount
        );

        $currentRewardBalance = $booking->user->fresh()->reward_points;

        // Check-in and check-out times.
        $checkInFrom = Carbon::parse(
            $booking->property->policy->check_in_from
        )->format('h:i A');

        $checkInUntil = Carbon::parse(
            $booking->property->policy->check_in_until
        )->format('h:i A');

        $checkOutFrom = Carbon::parse(
            $booking->property->policy->check_out_from
        )->format('h:i A');

        $checkOutUntil = Carbon::parse(
            $booking->property->policy->check_out_until
        )->format('h:i A');

        // Convert display amounts to integer minor units to avoid
        // independently rounded amounts disagreeing by one minor unit.
        $toMinorUnits = fn (float|string $amount): int =>
            (int) round((float) $amount * 100);

        $displayBookingTotalCents = $toMinorUnits(
            $currencyService->convert(
                $booking->total_price,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        $displayOriginalPriceCents = $toMinorUnits(
            $currencyService->convert(
                $booking->original_price,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        $displayDiscountAmountCents = $toMinorUnits(
            $currencyService->convert(
                $booking->discount_amount,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        $displayPaymentPortionCents = $currentPayment?->requested_amount !== null
            && strtoupper($currentPayment->requested_currency ?? '') === $invoiceCurrency
                ? $toMinorUnits($currentPayment->requested_amount)
                : $toMinorUnits(
                    $currencyService->convert(
                        $paymentPortion,
                        $baseCurrency,
                        $invoiceCurrency
                    )
                );

        $allPaidPaymentsHaveRequestedAmountInInvoiceCurrency =
            $paidPayments->isNotEmpty()
            && $paidPayments->every(
                fn (Payment $paidPayment) =>
                    $paidPayment->requested_amount !== null
                    && strtoupper($paidPayment->requested_currency ?? '') === $invoiceCurrency
            );

        $displayTotalPaidCents = $allPaidPaymentsHaveRequestedAmountInInvoiceCurrency
            ? $paidPayments->sum(
                fn (Payment $paidPayment) =>
                    $toMinorUnits($paidPayment->requested_amount)
            )
            : $toMinorUnits(
                $currencyService->convert(
                    $totalPaid,
                    $baseCurrency,
                    $invoiceCurrency
                )
            );

        // Derive the displayed balance so the displayed total, paid amount,
        // and balance add up consistently.
        $displayRemainingCents = max(
            0,
            $displayBookingTotalCents - $displayTotalPaidCents
        );

        $displayTotalRefundedCents = $toMinorUnits(
            $currencyService->convert(
                $totalRefunded,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        $displayTotalRewardDiscountCents = $toMinorUnits(
            $currencyService->convert(
                $totalRewardDiscount,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        $displayPaymentDiscountCents = $toMinorUnits(
            $currencyService->convert(
                $currentPayment?->discount_amount ?? 0,
                $baseCurrency,
                $invoiceCurrency
            )
        );

        // Payment::amount is the amount charged by Stripe in the app's
        // base currency. Do not convert it back to the user's currency.
        $displayPaymentAmount = (float) ($currentPayment?->amount ?? 0);

        $pdf = Pdf::loadView('invoices.invoice', [
            'booking' => $booking,
            'payment' => $currentPayment,

            'currency' => $invoiceCurrency,

            'originalPrice' => $displayOriginalPriceCents / 100,
            'bookingTotal' => $displayBookingTotalCents / 100,
            'discountAmount' => $displayDiscountAmountCents / 100,

            'paymentAmount' => $displayPaymentAmount,
            'paymentAmountCurrency' => $baseCurrency,
            'paymentDiscountAmount' => $displayPaymentDiscountCents / 100,
            'paymentRemaining' => $displayRemainingCents / 100,

            'portion' => $displayPaymentPortionCents / 100,
            'totalPaid' => $displayTotalPaidCents / 100,
            'totalRefunded' => $displayTotalRefundedCents / 100,
            'totalRewardDiscount' => $displayTotalRewardDiscountCents / 100,

            'totalEarnedPoints' => $totalEarnedPoints,
            'totalRedeemedPoints' => $totalRedeemedPoints,
            'currentRewardBalance' => $currentRewardBalance,

            'checkInFrom' => $checkInFrom,
            'checkInUntil' => $checkInUntil,
            'checkOutFrom' => $checkOutFrom,
            'checkOutUntil' => $checkOutUntil,
        ]);

        $fileName = 'invoices/' . $invoiceNumber . '.pdf';

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
