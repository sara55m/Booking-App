<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{

    private function recalculate(Review $review)
    {
        $review->property->recalculateRating();

        //clear cache for property details
        Cache::forget("property:{$review->property_id}");
    }
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->recalculate($review);
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
