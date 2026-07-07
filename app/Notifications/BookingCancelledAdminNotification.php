<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class BookingCancelledAdminNotification extends Notification implements ShouldQueue
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
            ->title(__('messages.booking_cancelled'))
            ->body(__('messages.booking_cancelled_admin_notification', [
                'reference' => $this->booking->reference,
                'customer' => $this->booking->user->name,
                'property' => $this->booking->property->name,
            ]))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
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
