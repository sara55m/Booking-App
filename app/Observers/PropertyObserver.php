<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class PropertyObserver
{

    private function clearCache(Property $property): void
    {
        Cache::forget("property:{$property->id}");
        Cache::tags(['properties'])->flush();
        Cache::forget("home:popular-cities");
    }
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        Cache::tags(['properties'])->flush();
        Cache::forget('home:popular-cities');
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {
        $this->clearCache($property);
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        $this->clearCache($property);
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        //
    }
}
