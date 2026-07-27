<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\BookingCancellationReason;
use App\Enums\BookingPaymentStatus;
use App\Events\BookingCancelled;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use DomainException;

class BookingCancellationService
{

    public function __construct(
        protected RefundService $refundService,
        protected RewardService $rewardService,
    ) {}


    public function cancel(Booking $booking){

        $booking->load([
            'property.policy',
            'payments',
            'user',
        ]);

        // Check if the booking can be cancelled (e.g., only if it's pending or confirmed)
        if (!in_array($booking->status, [BookingStatus::PENDING, BookingStatus::CONFIRMED])) {
            throw new DomainException(
                __('messages.booking_cannot_be_cancelled')
            );
        }

        //check property cancellation policy
        $policy=$booking->property->policy;
        if (! $policy) {
            throw new DomainException(
                __('messages.property_policy_not_found')
            );
        }

        $isRefundable = false;

        if ($policy->free_cancellation) {
            $deadline = $booking->check_in
                ->copy()
                ->subHours($policy->free_cancellation_hours);

            $isRefundable = now()->lessThanOrEqualTo($deadline);
        }

        //handle refund
        DB::transaction(function () use ($booking, $policy,$isRefundable) {
            //refund payments if the booking is refundable and cancel booking
            if ($isRefundable) {
                //get all paid payments for the booking
                $payments=$booking->payments()->where('status',PaymentStatus::PAID)->with('booking.user')->get();
                //refund each payment amount using stripe refund or reward points refund only
                foreach($payments as $payment)
                {
                    //calculate the refund amount based on the property cancellation policy
                    $refundAmount = round(
                        $payment->amount * ($policy->refund_percentage / 100),
                        2
                    );
                    //handle amount refund
                    $this->refundService->refund($payment,$refundAmount);
                    //handle the reward points reversing(full&partial)
                    $this->rewardService->reverse($payment,$refundAmount);
                }

                $paymentStatus = BookingPaymentStatus::REFUNDED;

            } else {
                // Booking cancelled but payment is kept as it was
                $paymentStatus = $booking->payment_status;
            }

            // Update the booking status to cancelled and booking payment status to refunded/leave it if no refund
            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancellation_reason'=>BookingCancellationReason::CUSTOMER_REQUESTED,
                'payment_status'=>$paymentStatus,
            ]);

        });
        $booking->load(['user']);

        //fire booking cancellation event
        event(new BookingCancelled($booking));
    }
}
