<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
        $this->booking->refresh();

        $path = storage_path(
            'app/public/' . $this->booking->invoice_path
        );

        return (new MailMessage)
            ->subject('Booking Confirmed')
            ->greeting('Hello '.$notifiable->name)

            ->line('Your booking has been confirmed successfully.')

            ->line('Booking Reference: '.$this->booking->reference)

            ->line('Property: '.$this->booking->property->name)

            ->line('Total Price: '.$this->booking->total_price.' EGP')

            ->line('Status: '.$this->booking->status->value)

            ->line('Payment Status: '.$this->booking->payment_status->value)

            ->line(
                'Check-in: ' .
                $this->booking->check_in
                    ->format('d F Y \a\t h:i:s a')
            )

            ->line(
                'Check-out: ' .
                $this->booking->check_out
                    ->format('d F Y \a\t h:i:s a')
            )

            ->line('Thank you for booking with us.')
            ->attach($path,[
                'as' => 'invoice.pdf',
                'mime' => 'application/pdf',
            ]);
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
