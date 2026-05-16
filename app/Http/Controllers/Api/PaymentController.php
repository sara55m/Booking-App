<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Enums\PaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\BookingPaymentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function checkout(Request $request,Booking $booking)
    {
        //validate amount to pay
        $request->validate([
            'amount'=>['required','numeric','min:1'],
        ]);
        //check user is authorized to make payment
        if($booking->user_id !== auth()->id()){
            return response()->json(['message' => __('messages.unauthorized_action')], 403);
        }

        //check if booking is already completed or cancelled
        if(in_array($booking->status,[BookingStatus::CANCELLED,BookingStatus::COMPLETED])){
            return response()->json(['message' => __('messages.booking_already_completed_or_cancelled')], 400);
        }

        //check if booking is already paid
        //get total amount for booking
        $paidAmount = $booking->payments()
        ->where('status', PaymentStatus::PAID)
        ->sum('amount');

        //calculate remaining amount
        $remainingAmount=$booking->total_price - $paidAmount;

        if($remainingAmount <= 0){
            return response()->json(['message' => __('messages.booking_already_paid')], 400);
        }

        $amountToPay=$request->amount;

        if($amountToPay > $remainingAmount){
            return response()->json(['message' => __('messages.amount_exceeds_the_remaining_balance')], 400);
        }

        //recalculate the remaining
        $newRemaining=$remainingAmount-$amountToPay;

        //stripe configuration
        Stripe::setApiKey(config('services.stripe.secret'));

        //use database transactions
        DB::beginTransaction();

        try {
            //create payment record with pending status
        $payment=Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amountToPay,
            'remaining' => $newRemaining,
            'status' => PaymentStatus::PENDING,
            'payment_method' => PaymentMethod::CARD,
        ]);

        //create checkout session
        $session=Session::create([
            'payment_method_types' =>[PaymentMethod::CARD],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'egp',
                    'product_data' => [
                        'name' => "Payment for booking #{$booking->id}",
                    ],
                    'unit_amount' => (int) round($amountToPay * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/payment-success',
            'cancel_url' => 'http://127.0.0.1:8000/payment-cancelled',
            'metadata' => [
            'payment_id' => $payment->id,
            ],
            ]);

            $payment->update([
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);

            DB::commit();

            return response()->json([
                'status_code'=>200,
                'message' => __('messages.checkout_session_created'),
                'session_id' => $session->id,
                'checkout_url' => $session->url,
            ]);

        }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' => __('messages.something_went_wrong'),

                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function webhook(Request $request)
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

                    $payment = Payment::with('booking')
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

                    $payment->update([

                        'stripe_payment_intent_id' =>
                            $session->payment_intent,

                        'transaction_id' =>
                            $session->payment_intent,

                        'paid_at' => now(),

                        'status' => PaymentStatus::PAID,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Update booking payment status
                    |--------------------------------------------------------------------------
                    */

                    $booking = $payment->booking;

                    $totalPaid = $booking->payments()
                        ->where('status', PaymentStatus::PAID)
                        ->sum('amount');

                    $remainingAmount =
                        $booking->total_price - $totalPaid;

                    if ($remainingAmount <= 0) {

                        $booking->update([

                            'status' =>
                                BookingStatus::CONFIRMED,

                            'payment_status' =>
                                BookingPaymentStatus::PAID,
                        ]);

                    } elseif ($remainingAmount > 0) {

                        $booking->update([

                            'payment_status' =>
                                BookingPaymentStatus::PARTIAL,
                            'status'=>
                                BookingStatus::CONFIRMED,
                        ]);
                    }

                    DB::commit();

                    break;

                } catch (\Exception $e) {

                    DB::rollBack();

                    Log::error('Stripe checkout.session.completed webhook failed', [

                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([

                        'message' =>
                            __('messages.something_went_wrong'),

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
