<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewCategory extends Model
{
    protected $fillable=['name','slug','is_active','sort_order'];

    public function review()
    {
        return $this->belongsToMany(Review::class,
        'review_category_ratings','review_category_id','review_id')
        ->withPivot('rating')
        ->withTimestamps();;
    }
}
