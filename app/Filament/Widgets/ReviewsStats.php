<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Review;
use App\Enums\ReviewStatus;

class ReviewsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalApprovedReviews = Review::where('status',ReviewStatus::Approved)->count() ?? 0;

        $totalPendingReviews = Review::where('status',ReviewStatus::Pending)->count() ?? 0;

        $lowRatingReviews = Review::where('status',ReviewStatus::Approved)->where('rating', '<=', 2)->count() ?? 0;

        return [
            //Total Approved Reviews
            Stat::make('Total Approved Reviews', $totalApprovedReviews)
                ->label(__('messages.total_approved_reviews'))
                ->color('success')
                ->icon('heroicon-o-chat-bubble-oval-left'),

            //Total Pending Reviews
            Stat::make('Total Pending Reviews', $totalPendingReviews)
            ->label(__('messages.total_pending_reviews'))
            ->color('warning')
            ->icon('heroicon-o-chat-bubble-oval-left'),

            //Low Rating Reviews
            Stat::make('Low Rating', $lowRatingReviews)
                ->label(__('messages.low_rating_reviews'))
                ->color('danger')
                ->icon('heroicon-o-face-frown'),
        ];



    }
}
