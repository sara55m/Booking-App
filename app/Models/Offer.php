<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\OfferStatus;

class Offer extends Model
{
    protected $fillable=
    [
        'property_id',
        'title',
        'code',
        'discount_type',
        'discount_value',
        'minimum_booking_amount',
        'minimum_nights',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'requires_coupon_code',
        'notify_users',
        'notification_sent_at',
    ];

    protected $casts=[
        'is_active'=>'boolean',
        'starts_at'=>'datetime',
        'ends_at'=>'datetime',
        'minimum_booking_amount'=>'decimal:2',
        'discount_value'=>'decimal:2',
        'usage_limit'=>'integer',
        'per_user_limit'=>'integer',
        'used_count'=>'integer',
        'requires_coupon_code'=>'boolean',
        'notify_users'=>'boolean',
        'notification_sent_at'=>'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return match ($this->discount_type) {
            'percentage' => "{$this->discount_value}%",
            'fixed' => 'EGP ' . number_format($this->discount_value, 2),
        };
    }

    public function scopeActive(Builder $query, $nights=1)
    {
        return $query
        ->where('is_active', 1)
        ->where('requires_coupon_code', 0)
        ->where(fn($q) => $q->whereNull('minimum_nights')->orWhere('minimum_nights', '<=', $nights))
        ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
        ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
        ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
        ->select('*')
        ->limit(1);
    }

    //scope offers for notification
    public function scopeReadyForNotification(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('notify_users', true)
            ->whereNull('notification_sent_at')
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    //compute offer status based on start and end dates
    protected function computedStatus(): Attribute
    {
        return Attribute::make(
            get: function (): OfferStatus {

                if (! $this->is_active) {
                    return OfferStatus::DISABLED;
                }

                if ($this->ends_at?->isPast()) {
                    return OfferStatus::EXPIRED;
                }

                if ($this->starts_at?->isFuture()) {
                    return OfferStatus::UPCOMING;
                }

                return OfferStatus::ACTIVE;
            }
        );
    }



}
