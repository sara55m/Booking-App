<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessExpiredBookingsJob;
use App\Jobs\CheckBookingBalanceDueJob;
use App\Jobs\CancelUnpaidOverdueBookingsJob;
use App\Jobs\MarkCompletedBookingsJob;
use App\Jobs\ProcessOfferNotificationsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//schedule bookings expiration job to run every minute
Schedule::job(new ProcessExpiredBookingsJob)->everyMinute();

//schedule bookings balance due check job to run daily
Schedule::job(new CheckBookingBalanceDueJob)->daily();

//schedule bookings cancellation job to run daily
Schedule::job(new CancelUnpaidOverdueBookingsJob)->daily();

//schedule mark bookings as completed job to run every hour
Schedule::job(new MarkCompletedBookingsJob)
    ->hourly();

//schedule active offers notification job to run daily
Schedule::job(new ProcessOfferNotificationsJob)->hourly();
