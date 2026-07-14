<?php

namespace App\Notifications;

use App\Enums\BookingPaymentStatus;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentCreatedAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Booking $booking,
        protected Payment $payment
    ) {
        $this->booking->loadMissing('user');
    }

    /**
     * Get the notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Store the notification in the database for Filament.
     */
    public function toDatabase(object $notifiable): array
    {
        $currency = strtoupper($this->booking->currency);

        $paymentType = $this->booking->total_price === $this->payment->amount
        ? __('messages.full_payment')
        : __('messages.partial_payment');

        return FilamentNotification::make()
            ->title(__('messages.booking_payment_received'))
            ->body(__('messages.booking_payment_succeeded_admin_notification', [
                'reference' => $this->booking->reference,
                'customer' => $this->booking->user->name,
                'payment_amount' => $currency . ' ' . number_format($this->payment->amount, 2),
                'remaining_amount' => $currency . ' ' . number_format($this->payment->remaining, 2),
                'payment_type' => $paymentType,
            ]))
            ->icon('heroicon-o-credit-card')
            ->iconColor('success')
            ->actions([
                Action::make('viewPayment')
                    ->label(__('messages.view_payment'))
                    ->url(
                        PaymentResource::getUrl('view', [
                            'record' => $this->payment,
                        ])
                    ),
            ])
            ->getDatabaseMessage();
    }
}
