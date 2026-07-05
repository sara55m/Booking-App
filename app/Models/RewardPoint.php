<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Models\User;
use App\Enums\RewardPointType;

class RewardPoint extends Model
{
    protected $fillable=[
        'user_id',
        'payment_id',
        'points',
        'type',
        'description',
    ];

    protected $casts=[
        'type' => RewardPointType::class,
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function payment(){
        return $this->belongsTo(Payment::class);
    }
}
