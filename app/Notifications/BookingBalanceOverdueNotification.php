<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\CurrencyService;

class BookingBalanceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->refresh();

        $this->booking->load('user');

        //currency data
        $currency = strtoupper(
            $this->booking->user->currency
            ?? config('app.currency', 'USD')
        );

        $baseCurrency = strtoupper(config('app.currency', 'USD'));

        $currencyService = app(CurrencyService::class);

        $bookingRemainingBalance=$currencyService->convert(
            $this->booking->remaining_balance,
            $baseCurrency,
            $currency
        );

        $formattedRemaining = number_format($bookingRemainingBalance, 2) . ' ' . $currency;

        return (new MailMessage)
            ->subject(__('messages.booking_balance_overdue_subject'))
            ->greeting(__('messages.booking_balance_overdue_greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('messages.booking_balance_overdue_body', [
                'reference' => $this->booking->reference,
            ]))
            ->line(__('messages.booking_balance_overdue_deadline',[
                'remaining'=>$formattedRemaining,
            ]))
            ->line(__('messages.booking_balance_overdue_closing'));
    }
}
