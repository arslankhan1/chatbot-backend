<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'question_key',
        'answer'
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id', 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_key', 'question_key');
    }
}
