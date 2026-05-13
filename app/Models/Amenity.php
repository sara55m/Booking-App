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

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'amenity_room', 'amenity_id', 'room_id')->withTimestamps();
    }
}
