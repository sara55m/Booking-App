<?php

namespace App\Listeners;

use App\Events\OfferActivated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Notifications\OfferNotification;

class SendOfferNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferActivated $event): void
    {
        $offer=$event->offer;

        //send all users active offer notification
        User::query()
        ->where('receive_marketing_emails', true)
        ->chunkById(500,function($users)use ($offer){
            foreach($users as $user){
                $user->notify(new OfferNotification($offer));
            }
        });
    }
}
