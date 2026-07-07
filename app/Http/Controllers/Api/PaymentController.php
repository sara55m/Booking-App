<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\BookingPaymentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\BookingPaymentConfirmed;
use App\Models\PaymentMethod as UserPaymentMethod;
use App\Services\StripeService;
use App\Services\RewardService;
use App\Models\User;
use App\Events\BookingPaymentFailed;

class PaymentController extends Controller
{
    public function checkout(
        Request $request,
        Booking $booking,
        StripeService $stripeService,
        RewardService $rewardService
    ) {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'redeem_points' => ['nullable', 'integer', 'min:0', 'multiple_of:100'],
        ]);

        $user = $booking->user;
        //lock uer update to prevent concurrent redemption
        $user = User::whereKey($user->id)
        ->lockForUpdate()
        ->first();

        if ($user->id !== auth()->id()) {
            return response()->json([
                'message' => __('messages.unauthorized_action'),
            ], 403);
        }

        if (in_array($booking->status, [
            BookingStatus::CANCELLED,
            BookingStatus::COMPLETED,
        ])) {
            return response()->json([
                'message' => __('messages.booking_already_completed_or_cancelled'),
            ], 400);
        }

        $totalPaid = $booking->payments()
            ->where('status', PaymentStatus::PAID)
            ->sum(DB::raw('amount + discount_amount'));

        $remainingAmount = $booking->total_price - $totalPaid;

        if ($remainingAmount <= 0) {
            return response()->json([
                'message' => __('messages.booking_already_paid'),
            ], 400);
        }

        $requestedAmount = $request->amount;

        if ($requestedAmount > $remainingAmount) {
            return response()->json([
                'message' => __('messages.amount_exceeds_the_remaining_balance'),
            ], 400);
        }

        $redeemPoints = $request->input('redeem_points', 0);

        if ($redeemPoints > $user->reward_points) {
            return response()->json([
                'message' => __('messages.redeem_points_exceeds_the_user_reward_points'),
            ], 422);
        }

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


        // Creates customer only if needed.
        // Any Stripe exception is logged inside the service.
        try {
            $stripeService->createCustomer($user);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }

        try {

            $payment = DB::transaction(function () use (
                $booking,
                $amountToCharge,
                $remainingAfterPayment,
                $redeemPoints,
                $discountAmount
            ) {

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $amountToCharge,
                    'remaining' => $remainingAfterPayment,
                    'redeemed_points' => $redeemPoints,
                    'discount_amount' => $discountAmount,
                    'status' => PaymentStatus::PENDING,
                    'payment_method' => PaymentMethod::CARD,
                ]);

                //if the amount to charge is 0 (fully paid using reward points),no need to create the session
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

        } catch (\Throwable $e) {

            Log::error('Checkout payment creation failed', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }

        // Fully paid using reward points
        if ($amountToCharge <= 0) {

            $rewardService->process(
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

        try {

            $session = $stripeService->createCheckoutSession(
                $user,
                $booking,
                $payment,
                $amountToCharge
            );

            DB::transaction(function () use ($payment, $session) {

                $payment->update([
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);
            });

            return response()->json([
                'status_code' => 200,
                'message' => __('messages.checkout_session_created'),
                'session_id' => $session->id,
                'checkout_url' => $session->url,
            ]);

        } catch (\Throwable $e) {

            $payment->update([
                'status' => PaymentStatus::FAILED,
            ]);

            Log::error('Checkout session creation failed', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            //fire payment failed event
            event(new BookingPaymentFailed($booking, $payment));

            return response()->json([
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    public function webhook(Request $request,RewardService $rewardService)
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

            $event = \Stripe\Webhook::constructEvent(
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

        } catch (\Stripe\Exception\SignatureVerificationException $e) {

            Log::error('Stripe webhook invalid signature', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('messages.invalid_signature')
            ], 400);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        /*
        |--------------------------------------------------------------------------
        | Handle Stripe events
        |--------------------------------------------------------------------------
        */

        switch ($event->type) {

            /*
            |--------------------------------------------------------------------------
            | Successful payment
            |--------------------------------------------------------------------------
            */

            case 'checkout.session.completed':

                DB::beginTransaction();

                try {

                    $session = $event->data->object;

                    $paymentId =
                        $session->metadata->payment_id ?? null;

                    if (!$paymentId) {

                        DB::rollBack();

                        return response()->json([
                            'message' => __('messages.payment_id_not_found_in_metadata')
                        ], 400);
                    }

                    $payment = Payment::with('booking.user','booking.property')
                        ->find($paymentId);

                    if (!$payment) {

                        DB::rollBack();

                        return response()->json([
                            'message' => __('messages.payment_not_found')
                        ], 404);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent duplicate webhook processing
                    |--------------------------------------------------------------------------
                    */

                    if ($payment->status === PaymentStatus::PAID) {

                        DB::rollBack();

                        return response()->json([
                            'message' => __('messages.payment_already_processed')
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update payment
                    |--------------------------------------------------------------------------
                    */

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

                    /*
                    |--------------------------------------------------------------------------
                    | Update booking payment status
                    |--------------------------------------------------------------------------
                    */

                    $booking = $payment->booking;

                    //consider adding the amount paid through reward points discount
                    $totalPaid = $booking->payments()
                        ->where('status', PaymentStatus::PAID)
                        ->sum(DB::raw('amount + discount_amount'));

                    $remainingAmount =
                        $booking->total_price - $totalPaid;

                    if ($remainingAmount <= 0) {

                        $booking->update([

                            'status' =>
                                BookingStatus::CONFIRMED,

                            'expires_at' => null,

                            'payment_status' =>
                                BookingPaymentStatus::PAID,
                        ]);

                    } elseif ($remainingAmount > 0) {

                        $booking->update([

                            'payment_status' =>
                                BookingPaymentStatus::PARTIAL,
                            'expires_at' => null,
                            'status'=>
                                BookingStatus::CONFIRMED,
                        ]);
                    }

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
                    $rewardService->process(
                        $booking->user,
                        $booking,
                        $payment
                    );

                    DB::commit();

                    //fire booking payment confirmation events
                    event(new BookingPaymentConfirmed($booking,$payment));

                    break;

                } catch (\Exception $e) {

                    DB::rollBack();

                    Log::error('Stripe checkout.session.completed webhook failed', [

                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'message' =>  __('messages.something_went_wrong'),
                    ], 500);
                }

            /*
            |--------------------------------------------------------------------------
            | Failed payment
            |--------------------------------------------------------------------------
            */

            case 'payment_intent.payment_failed':

                try {

                    $paymentIntent = $event->data->object;

                    $payment = Payment::where(
                        'stripe_payment_intent_id',
                        $paymentIntent->id
                    )->first();

                    if ($payment) {

                        $payment->update([

                            'status' =>
                                PaymentStatus::FAILED,
                        ]);
                    }

                    break;

                } catch (\Exception $e) {

                    Log::error('Stripe payment_intent.payment_failed webhook failed', [

                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([

                        'message' =>
                            __('messages.something_went_wrong'),

                    ], 500);
                }
        }

        /*
        |--------------------------------------------------------------------------
        | Success response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => __('messages.webhook_handled_successfully')
        ]);
    }
}
