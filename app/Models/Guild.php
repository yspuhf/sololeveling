<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guild extends Model
{
    protected $fillable = [
        'name',
        'description',
        'master_id',
        'xp',
        'level',
    ];

    protected $casts = [
        'master_id' => 'integer',
        'xp' => 'integer',
        'level' => 'integer',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guild_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }
}
