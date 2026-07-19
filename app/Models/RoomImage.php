<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    protected $fillable = [
        'property_id',
        'image',
        'is_cover',
        'sort_order',
        'caption',
    ];

    protected $casts=[
        'is_cover'=>'boolean',
        'sort_order'=>'integer'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
