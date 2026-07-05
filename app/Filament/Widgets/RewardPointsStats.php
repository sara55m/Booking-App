<?php

namespace App\Filament\Widgets;

use App\Models\RewardPoint;
use App\Models\User;
use App\Enums\RewardPointType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RewardPointsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalEarned = RewardPoint::where('type', RewardPointType::EARNED)
            ->sum('points');

        $totalRedeemed = RewardPoint::where('type', RewardPointType::REDEEMED)
            ->sum('points');

        $usersWithRewards = User::where('reward_points', '>', 0)
            ->count();

        $totalRewardBalance = User::sum('reward_points');

        return [
            Stat::make(__('messages.total_points_earned'), number_format($totalEarned))
                ->description(__('messages.points_earned'))
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make(__('messages.total_points_redeemed'), number_format($totalRedeemed))
                ->description(__('messages.points_redeemed'))
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),

            Stat::make(__('messages.users_with_rewards'), number_format($usersWithRewards))
                ->description(__('messages.users_having_reward_points'))
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make(__('messages.total_reward_balance'), number_format($totalRewardBalance))
                ->description(__('messages.current_points_owned_by_users'))
                ->color('warning')
                ->icon('heroicon-o-gift'),
        ];
    }
}
