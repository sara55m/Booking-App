<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityImage extends Model
{
    protected $fillable=[
        'city_id',
        'image',
        'sort_order',
        'is_cover',
        'caption'
    ];

    protected $casts=[
        'is_cover'=>'boolean',
        'sort_order'=>'integer'
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
