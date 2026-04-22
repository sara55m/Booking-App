<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = [
        'property_id',
        'image',
        'is_cover',
        'sort_order',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
