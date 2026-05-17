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


}
