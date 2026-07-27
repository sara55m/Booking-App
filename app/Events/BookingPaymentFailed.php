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
use App\Models\Payment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class BookingPaymentFailed implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Booking $booking,public Payment $payment)
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

            // Admins channel
            new PrivateChannel('admins'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'payment.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'booking' => [
                'id' => $this->booking->id,
                'status' => $this->booking->status->value,
                'payment_status'=>$this->booking->payment_status->value
            ],
            'payment' => [
                'id'=>$this->payment->id,
                'status' => $this->payment->status->value,
            ],

            'user' => [
                'id' => $this->booking->user_id,
                'name' => $this->booking->user->name,
            ],
        ];
    }
}
