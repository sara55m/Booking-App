<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use App\Models\Review;
use App\Observers\PropertyObserver;
use App\Observers\ReviewObserver;
use App\Models\Property;
use App\Models\City;
use App\Observers\CityObserver;
use App\Models\Offer;
use App\Observers\OfferObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['en','ar']);
        });

        //define review observer
        Review::observe(ReviewObserver::class);
        Property::observe(PropertyObserver::class);
        City::observe(CityObserver::class);
        Offer::observe(OfferObserver::class);
    }
}
