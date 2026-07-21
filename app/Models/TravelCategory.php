<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelCategory extends Model
{
    protected $fillable = ['name','slug','icon','sort_order','is_active'];

    public function cities()
    {
        return $this->belongsToMany(City::class,'city_travel_category');
    }
}
