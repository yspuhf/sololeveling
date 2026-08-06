<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyQuest extends Model
{
    protected $fillable = [
        'user_id',
        'quest_date',
        'description',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'quest_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
