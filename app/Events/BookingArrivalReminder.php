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

class BookingArrivalReminder implements ShouldBroadcast,ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Booking $booking)
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
            // Customer channel
            new PrivateChannel('users.' . $this->booking->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.arrival_reminder';
    }

    public function broadcastWith(): array
    {
        return [
            'booking' => [
                'id' => $this->booking->id,
                'status' => $this->booking->status->value,
                'payment_status'=>$this->booking->payment_status->value,
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
