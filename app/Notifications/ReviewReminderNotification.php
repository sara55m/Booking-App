<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class ReviewReminderNotification extends Notification implements ShouldQueue
{
    use Queueable,SerializesModels;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Booking $booking)
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
        ->subject(__('messages.review_reminder.subject'))
        ->greeting(__('messages.greeting', [
            'name' => $notifiable->name,
        ]))
    
        ->line(__('messages.review_reminder.introduction', [
            'property' => $this->booking->property->name,
        ]))
    
        ->line(__('messages.review_reminder.feedback_request'))
    
        ->action(
            __('messages.review_reminder.leave_review'),
            config('app.frontend_url') . "/bookings/{$this->booking->reference}/review"
        )
    
        ->line(__('messages.review_reminder.thank_you'));
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
