<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Events\BookingPaymentConfirmed;
use App\Services\RewardService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class CheckoutService
{
    public function __construct(
        private StripeService $stripeService,
        private RewardService $rewardService
    ) {}

    public function validateCheckout(
        Booking $booking,
        User $user,
        float $requestedAmount,
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

        $remainingAmount = $booking->total_price - $totalPaid;

        if ($remainingAmount <= 0) {
            throw ValidationException::withMessages([
                'message' => __('messages.booking_already_paid'),
            ]);
        }

        if ($requestedAmount > $remainingAmount) {
            throw ValidationException::withMessages([
                'message' => __('messages.amount_exceeds_the_remaining_balance'),
            ]);
        }

        if ($redeemPoints > $user->reward_points) {
            throw ValidationException::withMessages([
                'message' => __('messages.redeem_points_exceeds_the_user_reward_points'),
            ]);
        }

        return $remainingAmount;
    }   


    public function calculateAmounts(
        float $requestedAmount,
        int $redeemPoints,
        float $remainingAmount
    ): array {

        $discountAmount = min(
            intdiv($redeemPoints, config('rewards.redeem_rate'))
                * config('rewards.redeem_value'),
            $requestedAmount
        );

        $amountToCharge = max(
            0,
            $requestedAmount - $discountAmount
        );

        $remainingAfterPayment = $remainingAmount - $requestedAmount;

        return [
            'discountAmount' => $discountAmount,
            'amountToCharge' => $amountToCharge,
            'remainingAfterPayment' => $remainingAfterPayment,
        ];
    }

    public function createPayment(
        Booking $booking,
        float $amountToCharge,
        float $remainingAfterPayment,
        int $redeemPoints,
        float $discountAmount,
        string $idempotencyKey
    ): Payment {
        return DB::transaction(function () use (
            $booking,
            $amountToCharge,
            $remainingAfterPayment,
            $redeemPoints,
            $discountAmount,
            $idempotencyKey
        ) {
            //lock booking
            $booking = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->first();
            //check if a payment with the same idempotency key already exists
            $payment = Payment::where('idempotency_key', $idempotencyKey)
            ->first();

            if ($payment) {
                return $payment;
            }

            //otherwise, create a new payment record
            try {

                // Create a new payment
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $amountToCharge,
                    'remaining' => $remainingAfterPayment,
                    'redeemed_points' => $redeemPoints,
                    'discount_amount' => $discountAmount,
                    'status' => PaymentStatus::PENDING,
                    'payment_method' => PaymentMethod::CARD,
                    'idempotency_key' => $idempotencyKey,
                ]);
    
            } catch (\Illuminate\Database\QueryException $e) {

                //throw the exception if it's not a duplicate entry error
                if ($e->errorInfo[1] !== 1062) {
                    throw $e;
                }
    
                // Another request created the payment first.
                // Return the existing payment instead of failing.
                return Payment::where('idempotency_key', $idempotencyKey)
                    ->first();
            }
    
            // Fully paid using reward points
            if ($amountToCharge <= 0) {
    
                $payment->update([
                    'status' => PaymentStatus::PAID,
                    'paid_at' => now(),
                ]);
    
                $booking->update([
                    'status' => BookingStatus::CONFIRMED,
                    'payment_status' => BookingPaymentStatus::PAID,
                    'expires_at' => null,
                ]);
            }
    
            return $payment;
        });
    }

    public function completeRewardPayment(
        User $user,
        Booking $booking,
        Payment $payment
    ): JsonResponse {

        //if the payment is already completed, return a success response
        if (
            $payment->status === PaymentStatus::PAID &&
            $payment->paid_at
        ) {
            return response()->json([
                'status_code' => 200,
                'message' => __('messages.payment_completed_using_reward_points'),
            ]);
        }

        //otherwise, process the reward payment
        $this->rewardService->process(
            $user,
            $booking,
            $payment
        );

        event(new BookingPaymentConfirmed($booking, $payment));

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

}