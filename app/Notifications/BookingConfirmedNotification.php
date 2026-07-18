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
            ->subject(__('messages.booking_confirmed.subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))

            ->line(__('messages.booking_confirmed.introduction'))

            ->line(__('messages.booking_confirmed.booking_reference', [
                'reference' => $this->booking->reference,
            ]))

            ->line(__('messages.booking_confirmed.property', [
                'property' => $this->booking->property->name,
            ]))

            ->line(__('messages.booking_confirmed.total_price', [
                'amount' => number_format($this->booking->total_price, 2),
            ]))

            ->line(__('messages.booking_confirmed.booking_status', [
                'status' => ucfirst($this->booking->status->value),
            ]))

            ->line(__('messages.booking_confirmed.payment_status', [
                'status' => ucfirst($this->booking->payment_status->value),
            ]))

            ->line(__('messages.booking_confirmed.check_in', [
                'date' => $this->booking->check_in->format('d F Y \a\t h:i a'),
            ]))

            ->line(__('messages.booking_confirmed.check_out', [
                'date' => $this->booking->check_out->format('d F Y \a\t h:i a'),
            ]))

            ->line(__('messages.booking_confirmed.invoice_attached'))

            ->line(__('messages.booking_confirmed.thank_you'))
            
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
