<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'is_active',
        'is_featured',
        'description',
        'latitude',
        'longitude',
    ];

    protected $casts=[
        'is_active'=>'boolean',
        'is_featured'=>'boolean',
        'latitude'=>'decimal:2',
        'longitude'=>'decimal:2'
    ];

    public function images(){
        return $this->hasMany(CityImage::class);
    }

    public function coverImage()
    {
        return $this->hasOne(CityImage::class)
        ->where('is_cover', true);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
