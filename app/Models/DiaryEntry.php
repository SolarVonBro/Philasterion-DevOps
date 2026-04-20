<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recorded_at',
        'mood',
        'energy',
        'sleep_hours',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'mood'        => 'integer',
        'energy'      => 'integer',
        'sleep_hours' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}