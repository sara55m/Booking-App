<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewCategoryRating extends Model
{
 
    protected $fillable = [
        'review_id',
        'review_category_id',
        'rating',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function category()
    {
        return $this->belongsTo(ReviewCategory::class);
    }

}
