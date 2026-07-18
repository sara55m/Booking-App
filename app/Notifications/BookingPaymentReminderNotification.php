<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingPaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public Booking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('messages.payment_reminder.subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('messages.payment_reminder.introduction'))
            ->line(__('messages.payment_reminder.complete_payment'))
            ->line(__('messages.payment_reminder.expiration_time', [
                'minutes' => 15,
            ]))
            ->line(__('messages.payment_reminder.expiration_warning'))
            ->line(__('messages.payment_reminder.thank_you'));
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
