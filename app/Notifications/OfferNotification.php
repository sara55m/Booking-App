<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferNotification extends Notification implements ShouldQueue
{
    use Queueable,SerializesModels;


    /**
     * Create a new notification instance.
     */
    public function __construct(protected Offer $offer)
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
        $mail= (new MailMessage)
            ->subject(__('offers.offer.subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('offers.offer.introduction'))
            ->line(__('offers.offer.discount', [
                'title' => $this->offer->title,
                'discount' => $this->offer->formatted_discount,
            ]));
            
            if ($this->offer->ends_at) {
                $mail->line(
                    __('offers.offer.valid_until', [
                        'date' => $this->offer->ends_at->toFormattedDateString(),
                    ])
                );
            }
            return $mail
            ->action(
                __('messages.view_offer'),
                config('app.frontend_url').'/offers/'.$this->offer->id
            )
            ->line(__('messages.thank_you'));
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
