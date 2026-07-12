<?php

namespace App\Services;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingPaymentConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentMethod as UserPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Event;
use Stripe\Stripe;


class StripeWebhookService
{

    public function __construct(
        private RewardService $rewardService
    ) {
    }

    public function handle(Event $event): JsonResponse
    {
        switch ($event->type) {

            case 'checkout.session.completed':
                return $this->handleCheckoutCompleted($event);

            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailed($event);

            default:
                return response()->json([
                    'message' => __('messages.webhook_handled_successfully'),
                ]);
        }
    }

    private function handleCheckoutCompleted(Event $event): JsonResponse
    {
        //set stripe api key
        Stripe::setApiKey(config('services.stripe.secret'));
        try {

            DB::transaction(function () use ($event) {

                $session = $event->data->object;

                    $paymentId =
                        $session->metadata->payment_id ?? null;

                    if (! $paymentId) {
                        throw ValidationException::withMessages([
                            'message' => __('messages.payment_id_not_found_in_metadata'),
                        ]);
                    }

                    $payment = Payment::with('booking.user', 'booking.property')
                        ->lockForUpdate()
                        ->find($paymentId);

                    if (! $payment) {
                        throw ValidationException::withMessages([
                            'message' => __('messages.payment_not_found'),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent duplicate webhook processing (Idempotency)
                    |--------------------------------------------------------------------------
                    */

                    if ($payment->status === PaymentStatus::PAID) {
                        return;
                    }

                    //calculate earned points (100 Egp = 1 point)
                    $earnedPoints=intdiv($payment->amount , config('rewards.earn_rate'));

                    $payment->update([

                        'stripe_payment_intent_id' =>
                            $session->payment_intent,

                        'transaction_id' =>
                            $session->payment_intent,

                        'paid_at' => now(),

                        'status' => PaymentStatus::PAID,

                        'earned_points'=>$earnedPoints
                    ]);

                    //Update booking payment status
                    $booking = Booking::whereKey($payment->booking_id)
                        ->lockForUpdate()
                        ->first();

                    //consider adding the amount paid through reward points discount
                    $totalPaid = $booking->payments()
                        ->where('status', PaymentStatus::PAID)
                        ->sum(DB::raw('amount + discount_amount'));

                    $remainingAmount =
                        $booking->total_price - $totalPaid;

                    $booking->update([
                        'status' => BookingStatus::CONFIRMED,
                        'expires_at' => null,
                        'payment_status' => $remainingAmount <= 0
                            ? BookingPaymentStatus::PAID
                            : BookingPaymentStatus::PARTIAL,
                    ]);

                    $paymentIntent =
                        \Stripe\PaymentIntent::retrieve(
                            $session->payment_intent
                        );

                    $stripePaymentMethod =
                        \Stripe\PaymentMethod::retrieve(
                            $paymentIntent->payment_method
                        );

                        $isFirstPaymentMethod = ! UserPaymentMethod::where(
                            'user_id',
                            $booking->user_id
                        )->exists();

                        UserPaymentMethod::firstOrCreate(
                            [
                                'user_id' => $booking->user_id,
                                'fingerprint' => $stripePaymentMethod->card->fingerprint,
                            ],
                            [
                                'stripe_payment_method_id' => $stripePaymentMethod->id,
                                'brand' => $stripePaymentMethod->card->brand,
                                'last_four' => $stripePaymentMethod->card->last4,
                                'exp_month' => $stripePaymentMethod->card->exp_month,
                                'exp_year' => $stripePaymentMethod->card->exp_year,
                                'is_default' => $isFirstPaymentMethod,
                            ]
                        );

                        // Process rewards
                    $this->rewardService->process(
                        $booking->user,
                        $booking,
                        $payment
                    );

                    //fire booking payment confirmation events
                    event(new BookingPaymentConfirmed($booking,$payment));

            });

            return response()->json([
                'message' => __('messages.webhook_handled_successfully'),
            ]);

        } catch (\Throwable $e) {

            Log::error('Stripe checkout.session.completed webhook failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    private function handlePaymentFailed(Event $event): JsonResponse
    {
        try {

            DB::transaction(function () use ($event) {

                $paymentIntent = $event->data->object;
            
                $payment = Payment::where(
                    'stripe_payment_intent_id',
                    $paymentIntent->id
                )
                ->lockForUpdate()
                ->first();
            
                if (! $payment) {
                    return;
                }
            
                if ($payment->status === PaymentStatus::FAILED) {
                    return;
                }
            
                if ($payment->status === PaymentStatus::PAID) {
                    return;
                }
            
                $payment->update([
                    'status' => PaymentStatus::FAILED,
                ]);
            });

            return response()->json([
                'message' => __('messages.webhook_handled_successfully'),
            ]);

        } catch (\Throwable $e) {

            Log::error('Stripe payment_intent.payment_failed webhook failed', [
                'payment_intent_id' => $event->data->object->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }
}