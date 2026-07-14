<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use App\Models\Booking;

class PaymentSucceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking,public Payment $payment)
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
        //get the invoice path from the booking model and attach it to the email
        $this->booking->refresh();

        $path = storage_path(
            'app/public/' . $this->booking->invoice_path
        );
        
        $status = $this->booking->total_price === $this->payment->amount
            ? 'Full Payment'
            : 'Partial Payment';
        
        $currency = strtoupper($this->booking->currency);

        return (new MailMessage)
            ->subject('Payment Received')
            ->greeting('Hello '.$notifiable->name)

            ->line('We have successfully received your payment.')
            ->line('Booking Reference: '.$this->booking->reference)

            ->line('Booking Total: '.number_format($this->booking->total_price,2))

            ->line('Payment Amount: '.$currency." ".number_format($this->payment->amount, 2))

            ->line('Remaining Amount: '.$currency." ".number_format($this->payment->remaining, 2))

            ->line('Payment Type: '.$status)

            ->line('Thank you for booking with us.')

            ->attach($path,[
                'as' => 'invoice.pdf',
                'mime' => 'application/pdf',
            ])
            ->line('Your updated invoice is attached to this email.');
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
