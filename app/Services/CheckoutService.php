<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Events\BookingPaymentConfirmed;
use App\Events\PaymentSucceeded;
use App\Services\RewardService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\User;
use App\Services\CurrencyService;

class CheckoutService
{
    public function __construct(
        private StripeService $stripeService,
        private RewardService $rewardService
    ) {}

    public function validateCheckout(
        Booking $booking,
        User $user,
        int $requestedAmountCents,
        int $redeemPoints
    ) : float {

        if ($user->id !== auth()->id()) {
            throw new AuthorizationException(
                __('messages.unauthorized_action')
            );
        }

        if (in_array($booking->status, [
            BookingStatus::CANCELLED,
            BookingStatus::COMPLETED,
        ])) {
            throw ValidationException::withMessages([
                'message' => __('messages.booking_already_completed_or_cancelled'),
            ]);
        }

        $totalPaid = $booking->payments()
            ->where('status', PaymentStatus::PAID)
            ->sum(DB::raw('amount + discount_amount'));

        $totalPaidCents = (int) round((float) $totalPaid * 100);
        $bookingTotalCents=(int)round($booking->total_price*100);

        $remainingAmountCents = $bookingTotalCents - $totalPaidCents;

        if ($remainingAmountCents <= 0) {
            throw ValidationException::withMessages([
                'message' => __('messages.booking_already_paid'),
            ]);
        }

        if ($requestedAmountCents > $remainingAmountCents) {
            throw ValidationException::withMessages([
                'message' => __('messages.amount_exceeds_the_remaining_balance'),
            ]);
        }

        if ($redeemPoints > $user->reward_points) {
            throw ValidationException::withMessages([
                'message' => __('messages.redeem_points_exceeds_the_user_reward_points'),
            ]);
        }

        return $remainingAmountCents;
    }


    public function calculateAmounts(
        int $requestedAmountCents,
        int $redeemPoints,
        int $remainingAmountCents
    ): array {

        $redeemRate = max(1, (int) config('rewards.redeem_rate'));
        $redeemValueCents = (int) round(
            (float) config('rewards.redeem_value') * 100
        );

        $discountCents = min(
            intdiv($redeemPoints, $redeemRate)
                * $redeemValueCents,
            $requestedAmountCents
        );

        $amountToChargeCents = max(
            0,
            $requestedAmountCents - $discountCents
        );

        $remainingAfterPaymentCents = max(
            0,
            $remainingAmountCents - $requestedAmountCents
        );

        return [
            'discountCents' => $discountCents,
            'amountToChargeCents' => $amountToChargeCents,
            'remainingAfterPaymentCents' => $remainingAfterPaymentCents,
        ];
    }

    public function createPayment(
        Booking $booking,
        int $amountToChargeCents,
        int $remainingAfterPaymentCents,
        int $redeemPoints,
        int $discountCents,
        string $idempotencyKey
    ): array{
        return DB::transaction(function () use (
            $booking,
            $amountToChargeCents,
            $remainingAfterPaymentCents,
            $redeemPoints,
            $discountCents,
            $idempotencyKey
        ) {
            //lock booking
            $booking = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->first();

            //check if the booking is already confirmed
            $wasConfirmed= $booking->status === BookingStatus::CONFIRMED;

            //check if a payment with the same idempotency key already exists
            $payment = Payment::where('idempotency_key', $idempotencyKey)
            ->first();

            if ($payment) {
                return [
                    'payment' => $payment,
                    'booking' => $booking,
                    'wasConfirmed' => $wasConfirmed,
                ];
            }

            //otherwise, create a new payment record
            try {

                // Create a new payment
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => number_format($amountToChargeCents / 100, 2, '.', ''),
                    'remaining' => number_format($remainingAfterPaymentCents / 100, 2, '.', ''),
                    'discount_amount' => number_format($discountCents / 100, 2, '.', ''),
                    'redeemed_points' => $redeemPoints,
                    'status' => PaymentStatus::PENDING,
                    'payment_method' => PaymentMethod::CARD,
                    'idempotency_key' => $idempotencyKey,
                    'currency' => strtolower(config('app.currency', 'USD')),
                ]);

            } catch (\Illuminate\Database\QueryException $e) {

                //throw the exception if it's not a duplicate entry error
                if ($e->errorInfo[1] !== 1062) {
                    throw $e;
                }

                // Another request created the payment first.
                // Return the existing payment instead of failing.
                $payment=Payment::where('idempotency_key', $idempotencyKey)
                    ->first();
                return [
                    'payment' => $payment,
                    'booking' => $booking,
                    'wasConfirmed' => $wasConfirmed,
                ];
            }

            // Fully paid using reward points
            if ($amountToChargeCents <= 0) {

                $payment->update([
                    'payment_method' => PaymentMethod::WALLET,
                    'status' => PaymentStatus::PAID,
                    'paid_at' => now(),
                ]);

                $remainingAmount=$payment->remaining;

                $booking->update([
                    'status' => BookingStatus::CONFIRMED,
                    'payment_status' => $remainingAmount <= 0
                            ? BookingPaymentStatus::PAID
                            : BookingPaymentStatus::PARTIAL,
                    'expires_at' => null,
                ]);
            }

            return [
                'payment' => $payment,
                'booking' => $booking,
                'wasConfirmed' => $wasConfirmed,
            ];
        });
    }

    public function completeRewardPayment(
        User $user,
        Booking $booking,
        Payment $payment,
        bool $wasConfirmed
    ): JsonResponse {
        //process the reward payment
        $this->rewardService->process(
            $user,
            $booking,
            $payment
        );
        $booking->load('user');
        //fire the payment succeeded event
        event(new PaymentSucceeded($booking, $payment));
        //fire the booking confirmed event only if it is the first payment and the booking was not already confirmed
        if(! $wasConfirmed && $booking->status === BookingStatus::CONFIRMED){
            event(new BookingPaymentConfirmed($booking, $payment));
        }

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.payment_completed_using_reward_points'),
        ]);
    }

    public function createCheckoutSession(
        User $user,
        Booking $booking,
        Payment $payment,
        float $amountToCharge,
        string $idempotencyKey
    ) : JsonResponse {
        // Payment was already completed by a previous request
        if ($payment->status === PaymentStatus::PAID) {
            return response()->json([
                'status_code' => 200,
                'message' => __('messages.payment_already_completed'),
            ]);
        }
        if (! $payment->stripe_session_id) {
            //create a new checkout session
            $session = $this->stripeService->createCheckoutSession(
                $user,
                $booking,
                $payment,
                $amountToCharge,
                $idempotencyKey
            );

            $payment->update([
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);
        }else{
            //if the payment already has a stripe session id, return the existing session id and url
            $session = $this->stripeService->retrieveCheckoutSession($payment->stripe_session_id);
        }

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.checkout_session_created'),
            'session_id' => $session->id,
            'checkout_url' => $session->url,
        ]);
    }

    public function createStripeCustomer(
        User $user
    ): void {
        $this->stripeService->createCustomer($user);
    }

    public function convertToBaseCurrency(
    float $amount,
    User $user
    ): float {
        $currency = strtoupper(
            $user->currency ?? config('app.currency', 'USD')
        );

        $baseCurrency = strtoupper(config('app.currency', 'USD'));

        return app(CurrencyService::class)->convertToBaseUsingDisplayRate(
            $amount,
            $currency,
            $baseCurrency
        );
    }

}
