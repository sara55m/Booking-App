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
        'minimum_partial_payment_percentage',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'reviews_count' => 'integer',
        'average_rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'latitude'=>'decimal:2',
        'longitude'=>'decimal:2'
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
     * search by name or description
     */

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by rating
     */

     public function scopeMinimumRating($query, ?float $rating)
    {
        return $query->when($rating, function ($query) use ($rating) {
            $query->where('average_rating', '>=', $rating);
        });
    }

    /**
     * Filter by city
     */
    public function scopeCity($query, ?string $city)
    {
        return $query->when($city, function ($query) use ($city) {
            $query->whereHas('city', function ($query) use ($city) {
                $query->where('slug', $city);
            });
        });
    }

    /**
     * Filter by property type
     */
    public function scopeType($query, ?string $type)
    {
        return $query->when($type, function ($query) use ($type) {
            $query->whereHas('propertyType', function ($query) use ($type) {
                $query->where('slug',$type);
            });
        });
    }


    public function scopeMinPrice($query, ?float $price)
    {
        return $query->when($price, function ($query) use ($price) {
            $query->having('room_types_min_base_price', '>=', $price);
        });
    }

    public function scopeMaxPrice($query, ?float $price)
    {
        return $query->when($price, function ($query) use ($price) {
            $query->having('room_types_min_base_price', '<=', $price);
        });
    }

    public function scopeSort($query, ?string $sort)
    {
        //sort with [price_asc,price_desc,latest,rating]
        return match ($sort) {

            'price_asc' => $query->orderBy('room_types_min_base_price'),

            'price_desc' => $query->orderByDesc('room_types_min_base_price'),

            'rating' => $query
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count'),

            default => $query->latest(),
        };
    }

    public function scopePropertyAmenities($query, ?array $amenities)
    {
        return $query->when(! empty($amenities), function ($query) use ($amenities) {

            foreach ($amenities as $amenity) {

                $query->whereHas('amenities', function ($query) use ($amenity) {

                    $query->where('amenities.id', $amenity);

                });

            }

        });
    }

    public function scopeRoomAmenities($query, ?array $amenities)
    {
        return $query->when(! empty($amenities), function ($query) use ($amenities) {

            foreach ($amenities as $amenity) {

                $query->whereHas('roomTypes.amenities', function ($query) use ($amenity) {

                    $query->where('amenities.id', $amenity);

                });

            }

        });
    }
    /**
     * Availability filter : a property is available if it has at least one available room
     */
     public function scopeAvailable(
        $query,
        ?string $checkIn = null,
        ?string $checkOut = null,
        ?int $guests = null,
    )
    {
        return $query->when(
            $checkIn || $guests,
            function ($query) use ($checkIn, $checkOut, $guests) {

                $query->whereHas('roomTypes', function ($query) use ($checkIn, $checkOut, $guests) {

                    $query->available($checkIn, $checkOut, $guests);

                });

            }
        );
    }

    public function scopeWithActiveOffer($query,int $nights=1)
    {
        return $query->with([
            'offers' => fn ($q) => $q->active($nights)->limit(1)]);
    }

    //all filters scope
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->search($filters['search'] ?? null)
            ->city($filters['city'] ?? null)
            ->type($filters['type'] ?? null)
            ->minimumRating($filters['rating'] ?? null)
            ->minPrice($filters['min_price'] ?? null)
            ->maxPrice($filters['max_price'] ?? null)
            ->propertyAmenities($filters['property_amenities'] ?? null)
            ->roomAmenities($filters['room_amenities'] ?? null)
            ->available(
                $filters['check_in'] ?? null,
                $filters['check_out'] ?? null,
                $filters['guests'] ?? null
            )
            ->sort($filters['sort'] ?? null);
    }


}
