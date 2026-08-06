<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EliteSkill extends Model
{
    protected $fillable = [
        'user_id',
        'skill_name',
        'level',
        'xp',
        'sub_tracks_scores',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'level' => 'integer',
        'xp' => 'integer',
        'sub_tracks_scores' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
