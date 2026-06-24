<?php

namespace App\Observers;

use App\Models\PropertyType;
use Illuminate\Support\Facades\Cache;

class PropertyTypeObserver
{

    private function clearCache(PropertyType $propertyType): void
    {
        // Clear the cache for property types and home page
        Cache::tags(['home'])->forget('home:property-types');
        Cache::forget('property-type:' . $propertyType->id);
    }
    /**
     * Handle the PropertyType "created" event.
     */
    public function created(PropertyType $propertyType): void
    {
        $this->clearCache($propertyType);
    }

    /**
     * Handle the PropertyType "updated" event.
     */
    public function updated(PropertyType $propertyType): void
    {
        $this->clearCache($propertyType);
    }

    /**
     * Handle the PropertyType "deleted" event.
     */
    public function deleted(PropertyType $propertyType): void
    {
        $this->clearCache($propertyType);
    }

    /**
     * Handle the PropertyType "restored" event.
     */
    public function restored(PropertyType $propertyType): void
    {
        //
    }

    /**
     * Handle the PropertyType "force deleted" event.
     */
    public function forceDeleted(PropertyType $propertyType): void
    {
        //
    }
}
