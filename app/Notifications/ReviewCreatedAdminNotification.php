<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class ReviewCreatedAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Review $review)
    {
        $this->review->loadMissing([
            'user',
            'property',
            'booking'
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
            ->title(__('messages.review_created'))
            ->body(__('messages.review_created_admin_notification', [
                'booking' => $this->review->booking->reference,
                'user' => $this->review->user->name,
                'property' => $this->review->property->name,
            ]))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label(__('messages.view_review'))
                    ->url(ReviewResource::getUrl('view', [
                        'record' => $this->review,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->getDatabaseMessage();
    }
}
