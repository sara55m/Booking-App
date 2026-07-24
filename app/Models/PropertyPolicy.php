<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPolicy extends Model
{
    protected $fillable = [
        'property_id',

        'check_in_from',
        'check_in_until',
        'check_out_from',
        'check_out_until',

        'pets_allowed',
        'children_allowed',
        'smoking_allowed',

        'minimum_check_in_age',

        'cancellation_policy',
        'important_information',
    ];

    protected $casts=[
        'pets_allowed'=>'boolean',
        'children_allowed'=>'boolean',
        'smoking_allowed'=>'boolean',

        'minimum_check_in_age'=>'integer',
    ];

    public function property(){
        return $this->belongsTo(Property::class);
    }
}
