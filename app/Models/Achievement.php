<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'xp_reward',
        'gold_reward',
        'badge_path',
    ];

    protected $casts = [
        'xp_reward' => 'integer',
        'gold_reward' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}
