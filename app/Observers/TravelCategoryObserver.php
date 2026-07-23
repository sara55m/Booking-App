<?php

namespace App\Observers;

use App\Models\TravelCategory;
use Illuminate\Support\Facades\Cache;

class TravelCategoryObserver
{

    private function clearCache(TravelCategory $travelCategory){

        Cache::tags(['travel-categories'])->flush();

        Cache::tags(['cities'])->flush();

    }
    /**
     * Handle the TravelCategory "created" event.
     */
    public function created(TravelCategory $travelCategory): void
    {
        $this->clearCache($travelCategory);
    }

    /**
     * Handle the TravelCategory "updated" event.
     */
    public function updated(TravelCategory $travelCategory): void
    {
        $this->clearCache($travelCategory);
    }

    /**
     * Handle the TravelCategory "deleted" event.
     */
    public function deleted(TravelCategory $travelCategory): void
    {
        $this->clearCache($travelCategory);
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
