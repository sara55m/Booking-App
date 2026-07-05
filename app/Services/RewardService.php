<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\RewardPoint;
use App\Models\User;
use App\Enums\RewardPointType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RewardService
{
    public function process(User $user, Booking $booking, Payment $payment): void
    {
        DB::transaction(function () use ($user, $booking, $payment) {

            if ($payment->redeemed_points > 0) {

                $history = RewardPoint::firstOrCreate(
                    [
                        'payment_id' => $payment->id,
                        'type' => RewardPointType::REDEEMED,
                    ],
                    [
                        'user_id' => $user->id,
                        'points' => $payment->redeemed_points,
                        'description' => "Redeemed from payment #{$payment->id} for booking #{$booking->id}",
                    ]
                );

                if ($history->wasRecentlyCreated) {
                    $user->decrement('reward_points', $payment->redeemed_points);
                }
            }

            if ($payment->earned_points > 0) {

                $history = RewardPoint::firstOrCreate(
                    [
                        'payment_id' => $payment->id,
                        'type' => RewardPointType::EARNED,
                    ],
                    [
                        'user_id' => $user->id,
                        'points' => $payment->earned_points,
                        'description' => "Earned from payment #{$payment->id} from booking #{$booking->id}",
                    ]
                );

                if ($history->wasRecentlyCreated) {
                    $user->increment('reward_points', $payment->earned_points);
                }
            }
        });
    }

    public function getSummary(User $user){
        $rewardPoints=$user->reward_points;

        $rate = config('rewards.redeem_rate');
        $value = config('rewards.redeem_value');

        $rewardPointsBalance=intdiv($rewardPoints,$rate) * $value;

        //redeem points must be multiple of 100 (2450/100=24*100=2400 that can be used)
        $availableRedeemPoints=intdiv($rewardPoints,$rate) * $rate;

        //the next redemption required points(2450/100=24+1=25*100=2500)
        $nextRedeemAt=(intdiv($rewardPoints,$rate )+ 1) * $rate;

        return [
            'points'=>$rewardPoints,
            'balance'=>$rewardPointsBalance,
            'rate'=>$rate,
            'value'=>$value,
            'available_redeem_points'=>$availableRedeemPoints,
            'next_redeem_at'=>$nextRedeemAt
        ];
    }


    public function calculate(User $user,float $amount,int $redeemPoints) : array{

        // User can't redeem more than they own
        if ($redeemPoints > $user->reward_points) {
            throw ValidationException::withMessages([
                'points' => __('messages.redeem_points_exceeds_the_user_reward_points'),
            ]);
        }

        //ensure the points are multiples of 100
        $availableRedeemPoints =
            intdiv(
                $redeemPoints,
                config('rewards.redeem_rate')
            ) * config('rewards.redeem_rate');

        //ensure the discount amount is not greater than the amount to pay
        $discountAmount=min(
            $amount,
            intdiv($availableRedeemPoints,config('rewards.redeem_rate'))* config('rewards.redeem_value'));

        //amount to charge > 0
        $amountToCharge = round(max(0, $amount - $discountAmount), 2);

        //earned points
        $earnedPoints=intdiv((int)$amountToCharge,config('rewards.earn_rate'));

        return [
            'original_amount'=>$amount,
            "requested_points"=>$redeemPoints,
            'applied_points'=>$availableRedeemPoints,
            "unused_points"=>$redeemPoints-$availableRedeemPoints,
            'discount_amount'=>$discountAmount,
            'amount_to_charge'=>$amountToCharge,
            'earned_points'=>$earnedPoints
        ];
    }
}
