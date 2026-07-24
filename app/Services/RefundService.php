<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Stripe\Refund;
use Stripe\Stripe;

class RefundService
{
    public function refund(
        Payment $payment,
        float $refundAmount
    ): void {

        Stripe::setApiKey(
            config('services.stripe.secret')
        );

        if ($payment->stripe_payment_intent_id) {

            Refund::create([

                'payment_intent' =>
                    $payment->stripe_payment_intent_id,

                'amount' =>
                    (int) round($refundAmount * 100),

            ]);
        }

        $payment->update([

            'status' => PaymentStatus::REFUNDED,

            'refunded_amount' => $refundAmount,

            'refunded_at' => now(),

        ]);
    }
}
