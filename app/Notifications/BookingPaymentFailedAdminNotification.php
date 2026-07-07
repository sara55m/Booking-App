<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class BookingPaymentFailedAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Booking $booking,protected Payment $payment)
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
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('messages.booking_payment_failed'))
            ->body(__('messages.booking_payment_failed_admin_notification', [
                'reference' => $this->booking->reference,
                'customer' => $this->booking->user->name,
                'property' => $this->booking->property->name,
                'payment_amount' => number_format($this->payment->amount, 2) . ' EGP',
            ]))
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                Action::make('view')
                    ->label(__('messages.view_payment'))
                    ->url(PaymentResource::getUrl('view', [
                        'record' => $this->payment,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->getDatabaseMessage();
    }
}
