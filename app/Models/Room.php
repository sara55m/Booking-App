<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'property_id',
        'room_type_id',
        'description',
        'number',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
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

    //attributes
    public function getDisplayNameAttribute(): string
    {
        return "{$this->number} ({$this->roomType->name})";
    }
}
