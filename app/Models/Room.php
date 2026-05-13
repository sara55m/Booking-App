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
        'number',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function coverImage()
    {
        return $this->hasOne(RoomImage::class)
        ->where('is_cover', true);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room', 'room_id', 'amenity_id')->withTimestamps();
    }
}
