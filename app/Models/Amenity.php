<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'icon',
    ];

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'amenity_room_type', 'amenity_id', 'room_type_id')->withTimestamps();
    }
}
