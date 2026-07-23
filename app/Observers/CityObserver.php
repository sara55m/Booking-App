<?php

namespace App\Observers;

use App\Models\City;
use Illuminate\Support\Facades\Cache;

class CityObserver
{

    private function clearCache(City $city){
        Cache::tags(['home'])->forget('home:popular-cities');

        Cache::tags(['travel-categories'])->flush();

        Cache::tags(['cities'])->flush();
    }
    /**
     * Handle the City "created" event.
     */
    public function created(City $city): void
    {

    }

    public function saved(City $city){
        $this->clearCache($city);
    }

    /**
     * Handle the City "updated" event.
     */
    public function updated(City $city): void
    {

    }

    /**
     * Handle the City "deleted" event.
     */
    public function deleted(City $city): void
    {
        $this->clearCache($city);
    }

    /**
     * Handle the City "restored" event.
     */
    public function restored(City $city): void
    {
        //
    }

    /**
     * Handle the City "force deleted" event.
     */
    public function forceDeleted(City $city): void
    {
        //
    }
}
