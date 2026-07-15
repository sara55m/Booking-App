<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class BookingBalanceOverdueAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Booking $booking
    )
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
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('messages.balance_overdue_admin_title'))
            ->body(__('messages.balance_overdue_admin_body', [
                'reference' => $this->booking->reference,
                'customer' => $this->booking->user->name,
                'property' => $this->booking->property->name,
                'amount' => number_format($this->booking->remaining_balance, 2),
            ]))
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->actions([
                Action::make('view')
                    ->label(__('messages.view_booking'))
                    ->url(BookingResource::getUrl('view', [
                        'record' => $this->booking,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->getDatabaseMessage();
    }
    
}
