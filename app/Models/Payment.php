<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'status',
        'payment_method',
        'paid_at',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
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
}
