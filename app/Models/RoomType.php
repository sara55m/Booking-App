<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'description',
        'capacity',
        'base_price',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room_type', 'room_type_id', 'amenity_id')->withTimestamps();
    }

    //availability scope
    public function scopeAvailable(
        $query,
        ?string $checkIn = null,
        ?string $checkOut = null,
        ?int $guests = null,
    )
    {
        return $query
            ->when($guests, function ($query) use ($guests) {
                $query->where('capacity', '>=', $guests);
            })
            ->whereHas('rooms', function ($query) use ($checkIn, $checkOut) {
                $query->availableBetween($checkIn, $checkOut);
            });
    }
}
