<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable=['property_id','title','code','discount_type','discount_value','minimum_booking_amount','minimum_nights','is_active','starts_at','ends_at'];

    protected $casts=[
        'is_active'=>'boolean',
        'starts_at'=>'datetime',
        'ends_at'=>'datetime',
        'minimum_booking_amount'=>'decimal:2',
        'discount_value'=>'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

}
