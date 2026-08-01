<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlan extends Model
{
    protected $fillable = [
        'user_id',
        'conversation_id',
        'title',
        'country',
        'city',
        'days',
        'budget',
        'travel_style',
        'interests',
        'start_date',
        'end_date',
        'nights_count',
        'plan',
    ];

    protected $casts = [
        'interests' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class,'conversation_id');
    }
}
