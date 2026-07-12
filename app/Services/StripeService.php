<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe customer if one does not already exist.
     */
    public function createCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        try {
            $customer = Customer::create([
                'name'  => $user->name,
                'email' => $user->email,
            ]);

            $user->update([
                'stripe_customer_id' => $customer->id,
            ]);

            return $customer->id;

        } catch (\Throwable $e) {

            Log::error('Failed to create Stripe customer.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a Stripe Checkout Session.
     */
    public function createCheckoutSession(
        User $user,
        Booking $booking,
        Payment $payment,
        float $amount,
        string $idempotencyKey
    ): Session {
        try {

            return Session::create([
                'payment_method_types' => [
                    PaymentMethod::CARD->value,
                ],

                'payment_intent_data' => [
                    'setup_future_usage' => 'off_session',
                ],

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'egp',

                        'product_data' => [
                            'name' => "Payment for booking #{$booking->id}",
                        ],

                        'unit_amount' => (int) round($amount * 100),
                    ],

                    'quantity' => 1,
                ]],

                'mode' => 'payment',

                'success_url' => config('services.stripe.success_url'),

                'cancel_url' => config('services.stripe.cancel_url'),

                'customer' => $user->stripe_customer_id,

                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ],
            [
                'idempotency_key' => $idempotencyKey,
            ]
        );

        } catch (\Throwable $e) {

            Log::error('Failed to create Stripe Checkout Session.', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'user_id'    => $user->id,
                'amount'     => $amount,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        try {
            return Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve Stripe Checkout Session.', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
