<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ReviewCreatedAdminNotification;

class ReviewObserver
{

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

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        $this->recalculate($review);
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
