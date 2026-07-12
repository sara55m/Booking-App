<?php

namespace App\Models;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Models\RewardPoint;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'remaining',
        'status',
        'payment_method',
        'paid_at',
        'transaction_id',
        'currency',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'refunded_amount',
        'refunded_at',
        'earned_points',
        'redeemed_points',
        'discount_amount',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount'=>'decimal:2',
        'discount_amount'=>'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at'=>'datetime',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function rewardPoints(){
        return $this->hasMany(RewardPoint::class,'payment_id');
    }

    //accessors
    /**
     * Check if payment is completed
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status->value === 'paid';
    }

    /**
     * Check if payment failed
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->status->value === 'failed';
    }

    /**
     * Check if payment refunded
     */
    public function getIsRefundedAttribute(): bool
    {
        return $this->status->value === 'refunded';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Paid payments only
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Pending payments only
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Failed payments only
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Mark payment as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status'  => PaymentStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
        ]);
    }

    /**
     * Refund payment
     */
    public function refund(): void
    {
        $this->update([
            'status' => PaymentStatus::REFUNDED,
        ]);
    }

    // Automatically update booking status when payment is created, updated or deleted
    protected static function booted()
    {
        static::created(fn ($payment) => self::updateBookingStatus($payment));
        static::updated(fn ($payment) => self::updateBookingStatus($payment));
        static::deleted(fn ($payment) => self::updateBookingStatus($payment));
    }

    //update booking status and booking payment status
    protected static function updateBookingStatus($payment)
    {
        $booking = $payment->booking()->with('payments')->first();

        if (! $booking) return;

        $totalPaid = $booking->payments->sum('amount');

        if ($totalPaid >= $booking->total_price) {
            $booking->update(['payment_status'=>BookingPaymentStatus::PAID,'status' => BookingStatus::CONFIRMED]);
        } else {
            $booking->update(['payment_status'=>BookingPaymentStatus::PARTIAL,'status' => BookingStatus::CONFIRMED]);
        }
    }

}
