<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'trip_context',
        'nights_count',
    ];

    protected $casts = [
        'trip_context' => 'array',
        'nights_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiConversationMessage::class);
    }
}
