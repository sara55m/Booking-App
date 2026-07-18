<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\BookingPaymentStatus;
use App\Enums\PaymentStatus;
use App\Enums\BookingCancellationReason;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'property_id',
        'room_id',
        'check_in',
        'check_out',
        'guests_count',
        'nights_count',
        'total_price',
        'expires_at',
        'status',
        'payment_status',
        'offer_id',
        'original_price',
        'discount_amount',
        'invoice_number',
        'invoice_path',
        'balance_due_date',
        'cancellation_reason',
        'arrival_reminder_sent_at',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_price' => 'decimal:2',
        'guests_count' => 'integer',
        'nights_count' => 'integer',
        'status' => BookingStatus::class,
        'payment_status' => BookingPaymentStatus::class,
        'original_price'=>'decimal:2',
        'discount_amount'=>'decimal:2',
        'expires_at'=>'datetime',
        'balance_due_date' => 'date',
        'cancellation_reason' =>BookingCancellationReason::class,
        'arrival_reminder_sent_at'=>'datetime',
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

    public function getAmountPaidAttribute(): float
    {
        return round(
            $this->payments->where('status',PaymentStatus::PAID)->sum(DB::raw('amount + discount_amount')),
            2
        );
    }

    public function getRemainingBalanceAttribute(): float
    {
        return round($this->total_price - $this->amount_paid, 2);
    }

    // Check if the booking has not been fully paid yet 
    public function hasOutstandingBalance(): bool
    {
        return $this->remaining_balance > 0;
    }

    //check if the balance due date has passed and the booking still has an outstanding balance
    public function isBalanceOverdue(): bool
    {
        return $this->hasOutstandingBalance()
            && $this->balance_due_date !== null
            && $this->balance_due_date->isPast();
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    // Check if room is available for given dates
    public static function isRoomAvailable($roomId,$checkIn,$checkOut,$recordId = null): bool
    {
        return !self::where('room_id', $roomId)
            ->where('id', '!=', $recordId) // Exclude current booking when checking availability during update
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->where(function ($query) {
                $query->where('status', '!=', BookingStatus::PENDING)
                    ->orWhere('expires_at', '>', now());
            })//exclude expired pending bookings
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

    //calculate the minimum payment amount for booking partial payments
    public function getMinimumPaymentAmount(): float
    {
        $percentage = $this->property->minimum_partial_payment_percentage
            ?? config('booking.minimum_partial_payment_percentage', 30);

        return round($this->total_price * ($percentage / 100), 2);
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

    // Cancellable bookings (pending or confirmed)
    public function scopeCancelable(Builder $query)
    {
        return $query->whereIn('status', [
            'pending',
            'confirmed',
        ]);
    }

    /**
     * Bookings for specific user
     */
    public function scopeForUser(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    //scope for bookings whose check in is in one day
    public function scopeReadyForArrivalReminder(Builder $query): Builder
    {
        return $query
            ->where('status', BookingStatus::CONFIRMED)
            ->whereDate('check_in', now()->addDay()->toDateString())
            ->whereNull('arrival_reminder_sent_at');
    }

    //transition between statuses
    public function canTransitionTo(BookingStatus $newStatus): bool
    {

        if ($this->status === $newStatus) {
            return true;
        }
        return match ($this->status) {

            BookingStatus::PENDING => in_array($newStatus, [
                BookingStatus::CONFIRMED,
                BookingStatus::CANCELLED,
            ]),

            BookingStatus::CONFIRMED => in_array($newStatus, [
                BookingStatus::CHECKED_IN,
                BookingStatus::CANCELLED,
            ]),

            BookingStatus::CHECKED_IN => in_array($newStatus, [
                BookingStatus::CHECKED_OUT,
            ]),

            BookingStatus::CHECKED_OUT => in_array($newStatus, [
                BookingStatus::COMPLETED,
            ]),

            BookingStatus::COMPLETED => false,

            BookingStatus::CANCELLED => false,
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (is_null($booking->balance_due_date) && $booking->check_in) {
                $booking->balance_due_date = Carbon::parse($booking->check_in)->subDays(3);
            }
        });
    }

}
