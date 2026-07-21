<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name','iso_code','flag','currency','is_active'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
