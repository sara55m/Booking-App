<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingAutoCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Booking $booking)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('messages.booking_auto_cancelled_subject'))
            ->greeting(__('messages.balance_due_reminder_greeting', ['name' => $notifiable->name]))
            ->line(__('messages.booking_auto_cancelled_line1', [
                'reference' => $this->booking->reference,
                'property' => $this->booking->property->name,
            ]))
            ->line(__('messages.booking_auto_cancelled_line2'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
