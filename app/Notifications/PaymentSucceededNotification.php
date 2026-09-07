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
        // Get the latest booking data
        $this->booking->refresh();

        // Get the invoice path from the booking model and attach it to the email
        $path = storage_path(
            'app/public/' . $this->booking->invoice_path
        );

        $status = $this->booking->total_price === $this->payment->amount
            ? 'Full Payment'
            : 'Partial Payment';

        // User's preferred display currency
        $currency = $this->booking->user->currency
            ?? config('app.currency', 'USD');

        $currency = strtoupper($currency);

        $currencyService = app(\App\Services\CurrencyService::class);

        // Convert stored USD amounts only for display
        $bookingTotal = $currencyService->convert(
            $this->booking->total_price,
            config('app.currency', 'USD'),
            $currency
        );

        $paymentAmount = $currencyService->convert(
            $this->payment->amount,
            config('app.currency', 'USD'),
            $currency
        );

        $remainingAmount = $currencyService->convert(
            $this->payment->remaining,
            config('app.currency', 'USD'),
            $currency
        );

        return (new MailMessage)
            ->subject(__('messages.payment_received.subject'))

            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))

            ->line(__('messages.payment_received.introduction'))

            ->line(__('messages.payment_received.booking_reference', [
                'reference' => $this->booking->reference,
            ]))

            ->line(__('messages.payment_received.booking_total', [
                'currency' => $currency,
                'amount' => number_format($bookingTotal, 2),
            ]))

            ->line(__('messages.payment_received.payment_amount', [
                'currency' => $currency,
                'amount' => number_format($paymentAmount, 2),
            ]))

            ->line(__('messages.payment_received.remaining_amount', [
                'currency' => $currency,
                'amount' => number_format($remainingAmount, 2),
            ]))

            ->line(__('messages.payment_received.payment_type', [
                'type' => $status,
            ]))

            ->line(__('messages.payment_received.thank_you'))

            ->attach($path, [
                'as' => 'invoice.pdf',
                'mime' => 'application/pdf',
            ])

            ->line(__('messages.payment_received.invoice_attached'));
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
