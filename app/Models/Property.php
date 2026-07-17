<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'address',
        'description',
        'property_type_id',
        'rating',
        'is_active',
        'reviews_count',
        'average_rating',
        'is_featured',
        'minimum_partial_payment_percentage'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'reviews_count' => 'integer',
        'average_rating' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
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

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'property_id', 'user_id')->withTimestamps();
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

    /**
     * Average rating from reviews and reviews count
     */
    public function recalculateRating(): void
    {
        $approvedReviews = $this->approvedReviews()->get();

        $this->update([
            'reviews_count'=>$this->reviews_count = $approvedReviews->count(),
            'average_rating'=>$this->average_rating = round($approvedReviews->avg('rating') ?? 0,2),
        ]);
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
        return $query->whereHas('city',function ($q) use ($city){
            $q->where('name','like',"%{$city}%");
        });
    }

    /**
     * Filter by property type
     */
    public function scopeType($query, string $type)
    {
        return $query->whereHas('propertyType', function($q) use ($type) {
            $q->where('name','like',"%{$type}%");
        });
    }

    public function scopeWithActiveOffer($query, $nights=1)
    {
        return $query->with([
            'offers' => fn ($q) => $q->active($nights)->limit(1)]);
    }


}
