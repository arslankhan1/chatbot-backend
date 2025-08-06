<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_name',
        'user_country',
        'user_region',
        'requested_service',
        'conversation_history',
        'is_completed',
        'last_activity'
    ];

    protected $casts = [
        'conversation_history' => 'array',
        'is_completed' => 'boolean',
        'last_activity' => 'datetime'
    ];

    public function answers()
    {
        return $this->hasMany(SessionAnswer::class, 'session_id', 'session_id');
    }

    public function getAnswerByKey($key)
    {
        return $this->answers()->where('question_key', $key)->first()?->answer;
    }

    public function addToHistory($message, $type = 'user')
    {
        $history = $this->conversation_history ?? [];
        $history[] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
        $this->update(['conversation_history' => $history, 'last_activity' => now()]);
    }
}
