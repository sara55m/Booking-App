<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'name',
        'city',
        'address',
        'description',
        'type',
        'rating',
        'is_active',
        'reviews_count',
        'average_rating',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'reviews_count' => 'integer',
        'average_rating' => 'decimal:2',
    ];


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class,'property_id');
    }

    public function coverImage()
    {
        return $this->hasOne(PropertyImage::class)
            ->where('is_cover', true);
    }

    //attributes

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->where('status', 'approved')->count();
    }

    /**
     * Average rating from reviews
     */
    public function getAverageRatingAttribute(): float
    {
        return round(
            $this->reviews()
            ->where('status','approved')
            ->avg('rating') ?? 0,
            2
        );
    }

    //query scopes-->in search and filter

    /**
     * Active properties only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by city
     */
    public function scopeCity($query, string $city)
    {
        return $query->where('city','like', "%{$city}%");
    }

    /**
     * Filter by property type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type','like',"%{$type}%");
    }

    public function scopeWithActiveOffer($query, $nights=1)
    {
        return $query->with(['offers' => function ($q) use ($nights) {
            $q->where('is_active', 1)
            ->where('requires_coupon_code', 0)
            ->where(fn($q) => $q->whereNull('minimum_nights')->orWhere('minimum_nights', '<=', $nights))
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->select('*')
            ->limit(1);
        }]);
    }


}
