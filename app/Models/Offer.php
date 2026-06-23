<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'requires_coupon_code'
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
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query, $nights=1)
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



}
