<?php

namespace App\Observers;

use App\Models\TravelCategory;
use Illuminate\Support\Facades\Cache;

class TravelCategoryObserver
{
    /**
     * Handle the TravelCategory "created" event.
     */
    public function created(TravelCategory $travelCategory): void
    {
        Cache::tags(['travel-categories'])->flush();
    }

    /**
     * Handle the TravelCategory "updated" event.
     */
    public function updated(TravelCategory $travelCategory): void
    {
        Cache::tags(['travel-categories'])->flush();
    }

    /**
     * Handle the TravelCategory "deleted" event.
     */
    public function deleted(TravelCategory $travelCategory): void
    {
        Cache::tags(['travel-categories'])->flush();
    }

    /**
     * Handle the TravelCategory "restored" event.
     */
    public function restored(TravelCategory $travelCategory): void
    {
        //
    }

    /**
     * Handle the TravelCategory "force deleted" event.
     */
    public function forceDeleted(TravelCategory $travelCategory): void
    {
        //
    }
}
