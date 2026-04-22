<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'description',
        'price-per-night',
        'capacity',
        'quantity',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
