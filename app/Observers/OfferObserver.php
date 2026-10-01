<?php

namespace App\Observers;

use App\Models\Offer;
use Illuminate\Support\Facades\Cache;

class OfferObserver
{

    private function clearCache(): void
    {
        Cache::forget('cache_versions:properties');
        Cache::forget('cache_versions:home:featured-properties');
        Cache::forget('cache_versions:home:top-rated-properties');
        Cache::forget('cache_versions:home:deals-and-offers');
        Cache::forget('cache_versions:home:general-offers');
    }
    /**
     * Handle the Offer "created" event.
     */
    public function created(Offer $offer): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Offer "updated" event.
     */
    public function updated(Offer $offer): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Offer "deleted" event.
     */
    public function deleted(Offer $offer): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Offer "restored" event.
     */
    public function restored(Offer $offer): void
    {
        //
    }

    /**
     * Handle the Offer "force deleted" event.
     */
    public function forceDeleted(Offer $offer): void
    {
        //
    }
}
