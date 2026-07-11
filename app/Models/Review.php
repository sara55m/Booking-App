<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ReviewStatus;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'booking_id',
        'rating',
        'comment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function tags()
    {
        return $this->belongsToMany(ReviewTag::class, 'review_review_tags', 'review_id', 'review_tag_id')->withTimestamps();
    }

    protected static function booted()
    {
        static::saved(function ($review) {
            $property = $review->property;
            if ($property) {
                $reviewsCount = $property->reviews()->where('status', 'approved')->count();
                $averageRating = $property->reviews()->where('status', 'approved')->avg('rating');
                $property->reviews_count = $reviewsCount;
                $property->average_rating = round($averageRating,2);
                $property->save();
            }
        });
    }
}
