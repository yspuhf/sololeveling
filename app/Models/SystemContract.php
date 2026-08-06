<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemContract extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration_days',
        'difficulty',
        'xp_reward',
        'gold_reward',
        'status',
        'start_date',
        'end_date',
        'failed_at',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'xp_reward' => 'integer',
        'gold_reward' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'failed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        // When a contract is created, automatically generate the child check-in rows
        static::created(function (SystemContract $contract) {
            for ($day = 1; $day <= $contract->duration_days; $day++) {
                $contract->checkins()->create([
                    'day_number' => $day,
                    'is_checked' => false,
                    'completed_at' => null,
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(ContractCheckin::class, 'contract_id');
    }
}
