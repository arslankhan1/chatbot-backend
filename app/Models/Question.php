<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_key',
        'question_text',
        'order',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
