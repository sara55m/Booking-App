<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Offer;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class OfferActivated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Offer $offer)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Admins channel
            new PrivateChannel('admins'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'offer.activated';
    }

    public function broadcastWith(): array
    {
        return [
            'offer' => [
                'id' => $this->offer->id,
                'title' => $this->offer->title,
                'status' => $this->offer->status->value,
            ],

        ];
    }
}
