<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class BookingExpiredAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('messages.booking_expiring_soon'))
            ->body(__('messages.booking_expiring_soon_notification', [
                'booking' => $this->booking->reference,
                'expires_at'=>$this->booking->expires_at,
            ]))
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
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
