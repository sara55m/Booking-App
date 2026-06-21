<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{

    private function recalculate(Review $review)
    {
        $review->property->recalculateRating();
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
