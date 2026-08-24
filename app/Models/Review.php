<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ReviewStatus;
use App\Enums\ReviewRejectionReason;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'booking_id',
        'rating',
        'comment',
        'status',
        'approved_at',
        'rejection_reason',
        'rejection_note',
        'can_resubmit',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'approved_at'=>'datetime',
            'rejection_reason' => ReviewRejectionReason::class,
            'can_resubmit'=>'boolean',
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

    public function categories()
    {
        return $this->belongsToMany(ReviewCategory::class,
        'review_category_ratings','review_id','review_category_id')
        ->withPivot('rating')
        ->withTimestamps();;
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
