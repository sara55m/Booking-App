<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class BookingCompleted implements ShouldBroadcast,ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    //define booking
    public Booking $booking;
    /**
     * Create a new event instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking=$booking;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Customer channel
             new PrivateChannel('users.' . $this->booking->user_id),
        ];
    }
    public function broadcastAs(): string
    {
        return 'booking.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'booking' => [
                'id' => $this->booking->id,
                'status' => $this->booking->status->value,
                'created_at' => $this->booking->created_at->toIso8601String(),
            ],
            'property' => [
                'id' => $this->booking->property_id,
                'name' => $this->booking->property->name,
            ],
            'user' => [
                'id' => $this->booking->user_id,
                'name' => $this->booking->user->name,
        ],
        ];
    }
}
