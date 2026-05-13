<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewTag extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function reviews()
    {
        return $this->belongsToMany(Review::class, 'review_review_tags', 'review_tag_id', 'review_id')->withTimestamps();
    }
}
