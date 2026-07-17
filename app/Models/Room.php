<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;

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

    //availability scope
    public function scopeAvailableBetween(Builder $query,string $checkIn,string $checkOut) :Builder{
        return $query->whereDoesntHave('bookings', function (Builder $query) use ($checkIn, $checkOut) {
            $query
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->where(function (Builder $query) {
                $query->where('status', '!=', BookingStatus::PENDING)
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            });
        });
    }

    //guests number/capacity scope
    public function scopeForGuests(Builder $query,?int $guestsNumber) :Builder{
        if (! $guestsNumber) {
            return $query;
        }
        return $query->whereRelation(
            'roomType',
            'capacity',
            '>=',
            $guestsNumber
        );
    }

    //room type scope
    public function scopeForType(Builder $query,?int $roomTypeId) :Builder{
        if (! $roomTypeId) {
            return $query;
        }
        return $query->where('room_type_id', $roomTypeId);
        
    }
}
