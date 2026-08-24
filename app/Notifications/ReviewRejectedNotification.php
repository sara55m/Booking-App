<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Enums\ReviewRejectionReason;

class ReviewRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Review $review
    ) {
        $this->review->loadMissing('property');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('messages.review_rejected_subject'))
            ->greeting(__('messages.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('messages.review_rejected_message', [
                'property' => $this->review->property->name,
            ]))
            ->line(
                __('messages.rejection_reason') . ': ' .
                $this->getRejectionReason()
            );

        if ($this->review->rejection_note) {
            $mail->line(
                __('messages.rejection_note') . ': ' .
                $this->review->rejection_note
            );
        }

        if ($this->review->can_resubmit) {
            $mail->line(
                __('messages.review_can_be_resubmitted')
            );
        } else {
            $mail->line(
                __('messages.review_can_not_be_resubmitted')
            );
        }

        return $mail;
    }

    private function getRejectionReason(): string
    {
        return match ($this->review->rejection_reason) {
            ReviewRejectionReason::InappropriateContent =>
                __('messages.inappropriate_content'),

            ReviewRejectionReason::PersonalInformation =>
                __('messages.personal_information'),

            ReviewRejectionReason::IrrelevantContent =>
                __('messages.irrelevant_content'),

            ReviewRejectionReason::SpamOrPromotionalContent =>
                __('messages.spam_or_promotional_content'),

            ReviewRejectionReason::FakeOrSuspiciousReview =>
                __('messages.fake_or_suspicious_review'),

            ReviewRejectionReason::Other =>
                __('messages.other'),
        };
    }
}
