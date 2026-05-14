<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'check_in',
        'check_out',
        'guests_count',
        'nights_count',
        'total_price',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'guests_count' => 'integer',
        'nights_count' => 'integer',
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Check if room is available for given dates
    public static function isRoomAvailable($roomId,$checkIn,$checkOut,$recordId = null): bool
    {
        return !self::where('room_id', $roomId)
            ->where('id', '!=', $recordId) // Exclude current booking when checking availability during update
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->exists();
    }

    /**
     * Total number of nights
     */
    public function calculateNumberOfNights(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }
        return Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out));
    }

    /**
     * Total price including taxes and fees
     */
    public function calculateTotalPrice($pricePerNight):float
    {
        return $pricePerNight * $this->calculateNumberOfNights();
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
    public function scopeStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Upcoming bookings
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->whereDate('check_in', '>=', today());
    }

    /**
     * Bookings for specific user
     */
    public function scopeForUser(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected static function booted(){
        static::saving(function ($booking) {
            if(!$booking->room_id || !$booking->check_in || !$booking->check_out){
                return;
            }

            $room = Room::find($booking->room_id);

            if(!$room){
                return;
            }

            $booking->total_price = $booking->calculateTotalPrice($room->{'price-per-night'});

        });
    }


}
