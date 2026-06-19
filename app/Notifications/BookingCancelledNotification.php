<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Cancelled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been cancelled successfully.')
            ->line('Property: '.$this->booking->property->name)
            ->line('Check-in: '.$this->booking->check_in->format('d M Y'))
            ->line('Check-out: '.$this->booking->check_out->format('d M Y'))
            ->line('Total Price: '.$this->booking->total_price.' EGP')

            ->line('Status: '.$this->booking->status->value)

            ->line('Payment Status: '.$this->booking->payment_status->value)

            ->line('Your payment has been refunded successfully.')
            ->line('The refunded amount will be credited back to your original payment method within 2 business days.')
            ->line('If this cancellation was not made by you, please contact support immediately.')
            ->salutation('Thank you for using our platform.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
