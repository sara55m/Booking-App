<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessExpiredBookingsJob;
use App\Jobs\CheckBookingBalanceDueJob;
use App\Jobs\CancelUnpaidOverdueBookingsJob;
use App\Jobs\MarkCompletedBookingsJob;
use App\Jobs\ProcessOfferNotificationsJob;
use App\Jobs\SendArrivalRemindersJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//schedule bookings expiration job to run every minute
Schedule::job(new ProcessExpiredBookingsJob)
    ->everyMinute()
    ->withoutOverlapping();

//schedule bookings balance due check job to run daily
Schedule::job(new CheckBookingBalanceDueJob)
    ->dailyAt('08:00')
    ->withoutOverlapping();

//schedule bookings cancellation job to run every minute
Schedule::job(new CancelUnpaidOverdueBookingsJob)
    ->everyMinute()
    ->withoutOverlapping();

//schedule mark bookings as completed job to run every hour
Schedule::job(new MarkCompletedBookingsJob)
    ->hourly()
    ->withoutOverlapping();

//schedule active offers notification job to run every 5 minutes
Schedule::job(new ProcessOfferNotificationsJob)
    ->everyFiveMinutes()
    ->withoutOverlapping();

//schedule arrival reminder notification job to run daily
Schedule::job(new SendArrivalRemindersJob())
    ->dailyAt('08:00')
    ->withoutOverlapping();
