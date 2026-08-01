<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversationMessage extends Model
{
    protected $fillable = [
        'ai_conversation_id',
        'content',
        'role',
    ];

    public function aiConversation()
    {
        return $this->belongsTo(AiConversation::class);
    }
}
