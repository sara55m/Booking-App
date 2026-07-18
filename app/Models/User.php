<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Property;
use App\Models\RewardPoint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Translation\HasLocalePreference;

class User extends Authenticatable implements FilamentUser,MustVerifyEmail,HasLocalePreference
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'stripe_customer_id',
        'password',
        'role',
        'phone',
        'image',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'reward_points',
        'receive_marketing_emails',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'receive_marketing_emails'=>'boolean'
        ];
    }

    public function preferredLocale(): string
    {
        return $this->locale;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites','user_id','property_id')->withTimestamps();
    }

    public function paymentMethods(){
        return $this->hasMany(PaymentMethod::class);
    }

    public function rewardPoints(){
        return $this->hasMany(RewardPoint::class,'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }
}
