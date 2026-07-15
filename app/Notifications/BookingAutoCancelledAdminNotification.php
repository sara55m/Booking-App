<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class BookingAutoCancelledAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Booking $booking)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('messages.booking_auto_cancelled_admin_title'))
            ->body(__('messages.booking_auto_cancelled_admin_body', [
                'reference' => $this->booking->reference,
                'customer' => $this->booking->user->name,
                'property' => $this->booking->property->name,
            ]))
            ->icon('heroicon-o-x-circle')
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
