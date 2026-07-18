<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class ArrivalReminderNotification extends Notification implements ShouldQueue
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
        $mail = (new MailMessage)
            ->subject(__('messages.booking_arrival_reminder_subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('messages.booking_arrival_reminder_introduction'))
            ->line(__('messages.booking_reminder_property', [
                'property' => $this->booking->property->name,
            ]))
            ->line(__('messages.booking_reminder_check_in', [
                'date' => $this->booking->check_in->toFormattedDateString(),
            ]))
            ->line(__('messages.booking_reminder_guests', [
                'guests' => $this->booking->guests_count,
            ]));

        if ($this->booking->room) {
            $mail->line(__('messages.booking_reminder_room', [
                'room' => $this->booking->room->display_name,
            ]));
        }

        return $mail
            ->action(
                __('messages.view_booking'),
                config('app.frontend_url') . '/bookings/' . $this->booking->reference
            )
            ->line(__('messages.booking_arrival_reminder_closing'));
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
