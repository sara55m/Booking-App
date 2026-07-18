<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Enums\PaymentStatus;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking)
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
        $refundedAmount=$this->booking->payments()->where('status',PaymentStatus::REFUNDED)->sum('amount');

        $reversedPoints=$this->booking->payments()->sum('earned_points');

        $returnedPoints=$this->booking->payments()->sum('redeemed_points');

        return (new MailMessage)
        ->subject(__('messages.booking_cancelled.subject'))
        ->greeting(__('messages.greeting', [
            'name' => $notifiable->name,
        ]))
        ->line(__('messages.booking_cancelled.introduction'))
        ->line(__('messages.booking_cancelled.booking_reference', [
            'reference' => $this->booking->reference,
        ]))
        ->line(__('messages.booking_cancelled.property', [
            'property' => $this->booking->property->name,
        ]))
        ->line(__('messages.booking_cancelled.check_in', [
            'date' => $this->booking->check_in->format('d M Y'),
        ]))
        ->line(__('messages.booking_cancelled.check_out', [
            'date' => $this->booking->check_out->format('d M Y'),
        ]))
        ->line(__('messages.booking_cancelled.cancellation_date', [
            'date' => now()->format('d M Y H:i'),
        ]))

        ->line('')

        ->line(__('messages.booking_cancelled.refund_summary'))

        ->line(__('messages.booking_cancelled.refunded_amount', [
            'amount' => number_format($refundedAmount, 2),
        ]))

        ->line(__('messages.booking_cancelled.payment_status', [
            'status' => ucfirst($this->booking->payment_status->value),
        ]))

        ->line(__('messages.booking_cancelled.booking_status', [
            'status' => ucfirst($this->booking->status->value),
        ]))

        ->line('')

        ->line(__('messages.booking_cancelled.reward_points'))

        ->line(__('messages.booking_cancelled.returned_reward_points', [
            'points' => $returnedPoints,
        ]))

        ->line(__('messages.booking_cancelled.reversed_earned_points', [
            'points' => $reversedPoints,
        ]))

        ->line(__('messages.booking_cancelled.current_reward_balance', [
            'points' => $notifiable->fresh()->reward_points,
        ]))

        ->line('')

        ->line(__('messages.booking_cancelled.refund_notice'))

        ->line(__('messages.booking_cancelled.contact_support'))

        ->salutation(__('messages.thank_you'));
            
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
