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
            ->subject('Booking Cancelled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been cancelled successfully.')
            ->line('Booking Reference: '.$this->booking->reference)
            ->line('Property: '.$this->booking->property->name)
            ->line('Check-in: '.$this->booking->check_in->format('d M Y'))
            ->line('Check-out: '.$this->booking->check_out->format('d M Y'))
            ->line('Cancellation Date: '.now()->format('d M Y H:i'))

            ->line('')

            ->line('Refund Summary')

            ->line('Refunded Amount: '.number_format($refundedAmount, 2).' EGP')

            ->line('Payment Status: '.ucfirst($this->booking->payment_status->value))

            ->line('Booking Status: '.ucfirst($this->booking->status->value))

            ->line('')

            ->line('Reward Points')

            ->line('Returned Reward Points: '.$returnedPoints)
            ->line('Reversed Earned Points: '.$reversedPoints)
            ->line('Current Reward Balance: '.$notifiable->fresh()->reward_points)

            ->line('')

            ->line('The refund has been initiated successfully.')
            ->line('Depending on your bank or card issuer, the refunded amount may take 2-7 business days to appear on your statement.')

            ->line('If you did not request this cancellation or have any questions, please contact our support team.')

            ->salutation('Thank you for using our platform.');
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
