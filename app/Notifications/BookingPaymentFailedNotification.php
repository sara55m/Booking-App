<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\Payment;

class BookingPaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking,public Payment $payment,)
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
            ->subject(__('messages.payment_failed_notification.subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('messages.payment_failed_notification.introduction'))
            ->line(__('messages.payment_failed_notification.booking_reference', [
                'reference' => $this->booking->reference,
            ]))
            ->line(__('messages.payment_failed_notification.property', [
                'property' => $this->booking->property->name,
            ]))
            ->line(__('messages.payment_failed_notification.amount', [
                'amount' => number_format($this->payment->amount, 2),
            ]))
            ->line(__('messages.payment_failed_notification.reason'))
            ->action(
                __('messages.payment_failed_notification.complete_payment'),
                url("/bookings/{$this->booking->id}/checkout")
            )
            ->line(__('messages.payment_failed_notification.expiration_notice', [
                'expires' => $this->booking->expires_at?->format('d M Y H:i'),
            ]))
            ->line(__('messages.payment_failed_notification.contact_support'))
            ->salutation(__('messages.thank_you'));
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
