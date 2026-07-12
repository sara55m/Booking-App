<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\StripeWebhookService;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Services\CheckoutService;
use App\Events\BookingPaymentFailed;
use Illuminate\Http\JsonResponse;
use App\Enums\PaymentStatus;

class PaymentController extends Controller
{
    public function checkout(
        Request $request,
        Booking $booking,
        CheckoutService $checkoutService
    ) {
        $validated=$request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'redeem_points' => ['nullable', 'integer', 'min:0', 'multiple_of:100'],
        ]);

        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json([
                'message' => __('messages.idempotency_key_required'),
            ], 400);
        }

        //validate checkout
        $requestedAmount = (float) $validated['amount'];
        $redeemPoints = (int) ($validated['redeem_points'] ?? 0);
        $user = $booking->user;

        $remainingAmount = $checkoutService->validateCheckout(
            $booking,
            $user,
            $requestedAmount,
            $redeemPoints
        );

        //calculate amounts
        [
            'discountAmount' => $discountAmount,
            'amountToCharge' => $amountToCharge,
            'remainingAfterPayment' => $remainingAfterPayment,
        ] = $checkoutService->calculateAmounts(
            $requestedAmount,
            $redeemPoints,
            $remainingAmount
        );

        // Creates customer only if needed.
        // Any Stripe exception is logged inside the service.
        try {
            $checkoutService->createStripeCustomer($user);
        
        } catch (\Throwable $e) {
        
            Log::error('Stripe customer creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        
            return response()->json([
                'message' => __('messages.something_went_wrong_in_stripe_customer_creation'),
            ], 500);
        }
        
        try {

            $payment = $checkoutService->createPayment(
                $booking,
                $amountToCharge,
                $remainingAfterPayment,
                $redeemPoints,
                $discountAmount,
                $idempotencyKey
            );
        
        } catch (\Throwable $e) {
        
            Log::error('Checkout payment creation failed', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        
            return response()->json([
                'message' => __('messages.something_went_wrong_in_checkout_payment_creation'),
            ], 500);
        }

        //if the amount to charge is zero or less, complete the payment using reward points
        if ($amountToCharge <= 0) {
            return $checkoutService->completeRewardPayment(
                $user,
                $booking,
                $payment
            );
        }

        //otherwise create Stripe checkout session
        try {

            return $checkoutService->createCheckoutSession(
                $user,
                $booking,
                $payment,
                $amountToCharge,
                $idempotencyKey
            );
        } catch (\Throwable $e) {

            Log::error('Checkout session creation failed', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update([
                'status' => PaymentStatus::FAILED,
            ]);

            //fire payment failed event
            event(new BookingPaymentFailed($booking, $payment));

            return response()->json([
                'message' => __('messages.something_went_wrong_in_stripe_session_creation'),
            ], 500);
        }
        
    }

    public function webhook(Request $request, StripeWebhookService $stripeWebhookService) : JsonResponse
    {
        $payload = $request->getContent();

        $signature = $request->server('HTTP_STRIPE_SIGNATURE');

        $webhookSecret = config('services.stripe.webhook_secret');

        /*
        |--------------------------------------------------------------------------
        | Verify Stripe webhook signature
        |--------------------------------------------------------------------------
        */

        try {

            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

        } catch (\UnexpectedValueException $e) {

            Log::error('Stripe webhook invalid payload', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.invalid_payload')
            ], 400);

        } catch (SignatureVerificationException $e) {

            Log::error('Stripe webhook invalid signature', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.invalid_signature')
            ], 400);
        }

        //handle the event using the StripeWebhookService
        return $stripeWebhookService->handle($event);        
    }   
}
