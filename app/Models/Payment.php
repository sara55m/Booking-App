<?php

namespace App\Models;

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

}
