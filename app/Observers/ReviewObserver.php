<?php

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ReviewCreatedAdminNotification;
use App\Jobs\GenerateReviewSummaryJob;
use App\Services\ReviewSummaryService;

class ReviewObserver
{

    public function __construct(
            private ReviewSummaryService $reviewSummaryService
        ) {
        }

    private function recalculate(Review $review)
    {
        $review->property->recalculateRating();

        //clear cache for property details
        Cache::forget("property:{$review->property_id}");
        Cache::tags(['properties'])->flush();
        Cache::tags(['home'])->forget('home:featured-properties');
        Cache::tags(['home'])->forget('home:top-rated-properties');
    }
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->recalculate($review);
        //send review created notification to admins
        $admins=User::where('role','admin')->get();

        Notification::send(
            $admins,
            new ReviewCreatedAdminNotification($review)
        );

    }

    public function updating(Review $review): void
    {
        if (! $review->isDirty('status')) {
            return;
        }

        if ($review->status === ReviewStatus::Approved) {
            $review->approved_at = now();
        } else {
            $review->approved_at = null;
        }
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        $this->recalculate($review);

        $shouldRegenerate = $this->reviewSummaryService
            ->shouldRegenerateForReview($review);

        if ($shouldRegenerate) {
            GenerateReviewSummaryJob::dispatch($review->property_id);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->recalculate($review);
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
