<?php

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ReviewCreatedAdminNotification;
use App\Notifications\ReviewUpdatedAdminNotification;
use App\Jobs\GenerateReviewSummaryJob;
use App\Services\ReviewSummaryService;
use App\Notifications\ReviewApprovedNotification;
use App\Notifications\ReviewRejectedNotification;

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
            $review->rejection_reason = null;
            $review->rejection_note = null;
            $review->can_resubmit = false;
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

        //send admin notification when an approved review is updated
        $oldStatus = $review->getRawOriginal('status');
        $newStatus = $review->status;

        if (
            ($oldStatus === ReviewStatus::Approved->value || $oldStatus === ReviewStatus::Rejected->value ) &&
            $newStatus === ReviewStatus::Pending
        ) {
            $admins=User::where('role','admin')->get();
            Notification::send(
                $admins,
                new ReviewUpdatedAdminNotification($review)
            );
        }

        //send mail notification to the user when a review is approved
        if (
            $oldStatus !== ReviewStatus::Approved->value &&
            $newStatus === ReviewStatus::Approved
        ) {
            $review->user->notify(new ReviewApprovedNotification($review));
        }

        //send mail notification to the user when a review is rejected mentioning the rejection reason
        if (
            $oldStatus !== ReviewStatus::Rejected->value &&
            $newStatus === ReviewStatus::Rejected
        ) {
            $review->user->notify(new ReviewRejectedNotification($review));
        }

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

        //if the deleted review was an approved review -->regenerate the ai summary
        if ($review->status === ReviewStatus::Approved) {
            GenerateReviewSummaryJob::dispatch($review->property_id);
        }
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
