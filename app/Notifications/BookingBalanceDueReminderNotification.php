<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Services\CurrencyService;

class BookingBalanceDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Booking $booking)
    {
        $this->booking->loadMissing([
            'user',
            'property',
        ]);
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

        $this->booking->load([
            'user',
            'property',
        ]);

        //currency data
        $currency = strtoupper(
            $this->booking->user->currency
            ?? config('app.currency', 'USD')
        );

        $baseCurrency = config('app.currency', 'USD');

        $currencyService = app(CurrencyService::class);

        $bookingRemainingBalance=$currencyService->convert(
            $this->booking->remaining_balance,
            $baseCurrency,
            $currency
        );

        return (new MailMessage)
            ->subject(__('messages.balance_due_reminder_subject'))
            ->greeting(__('messages.balance_due_reminder_greeting', ['name' => $notifiable->name]))
            ->line(__('messages.balance_due_reminder_line1', [
                'reference' => $this->booking->reference,
                'property' => $this->booking->property->name,
            ]))
            ->line(__('messages.balance_due_reminder_line2', [
                'amount' => number_format($bookingRemainingBalance, 2) . ' ' . $currency,
                'date' => $this->booking->balance_due_date->format('Y-m-d'),
            ]))
            ->line(__('messages.balance_due_reminder_line3'));
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
