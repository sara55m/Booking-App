<?php

namespace App\Jobs;

use App\Events\OfferActivated;
use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessOfferNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Offer::readyForNotification()
        ->chunkById(100,function($offers){
            foreach ($offers as $offer) {
                //fire offer activated event to send offer notifications
                DB::transaction(function () use ($offer) {
                    $offer->update([
                        'notification_sent_at' => now(),
                    ]);

                    //make sure the event is fired after the database update to prevent duplicate offer notifications
                    DB::afterCommit(function () use ($offer) {
                        OfferActivated::dispatch($offer);
                    });
                });
            }
        });
    }
}
