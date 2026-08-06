<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractCheckin extends Model
{
    protected $fillable = [
        'contract_id',
        'day_number',
        'completed_at',
        'is_checked',
    ];

    protected $casts = [
        'contract_id' => 'integer',
        'day_number' => 'integer',
        'is_checked' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(SystemContract::class, 'contract_id');
    }
}
