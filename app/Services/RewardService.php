<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\RewardPoint;
use App\Models\User;
use App\Enums\RewardPointType;
use Illuminate\Support\Facades\DB;

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
                        'description' => "Redeemed for booking #{$booking->id}",
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
                        'description' => "Earned from booking #{$booking->id}",
                    ]
                );

                if ($history->wasRecentlyCreated) {
                    $user->increment('reward_points', $payment->earned_points);
                }
            }
        });
    }
}
