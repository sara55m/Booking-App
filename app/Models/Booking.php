<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'status' => BookingStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }


    //attributes

    /**
     * Total number of nights
     */
    public function getNumberOfNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    /**
     * Total price including taxes and fees
     */
    public function getTotalPriceAttribute()
    {
        return $this->room->price_per_night * $this->number_of_nights;
    }

    /**
     * Whether check-in date has passed
     */
    public function getHasStartedAttribute(): bool
    {
        return now()->greaterThanOrEqualTo($this->check_in);
    }

    //query scopes-->in search and filter

    /**
     * Filter by booking status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('check_in', '>=', today());
    }

    /**
     * Bookings for specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

}
