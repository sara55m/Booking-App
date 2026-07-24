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

        if(! $policy->free_cancellation){
            //no refund
            throw new DomainException(
                __('messages.booking_is_non_refundable')
            );
        }

        $deadline = $booking->check_in
        ->copy()//to avoid updating the actual check in value
        ->subHours($policy->free_cancellation_hours);

        if(now()->greaterThan($deadline)){
            //no refund
            throw new DomainException(
                __('messages.free_cancellation_period_has_expired')
            );
        }

        //handle refund
        DB::transaction(function () use ($booking, $policy) {
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

            // Update the booking status to cancelled and booking payment status to refunded
            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancellation_reason'=>BookingCancellationReason::CUSTOMER_REQUESTED,
                'payment_status'=>BookingPaymentStatus::REFUNDED,
            ]);

        });

        //fire booking cancellation event
        event(new BookingCancelled($booking));
    }
}
