<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        'contract_limit',
        'elite_skill_access',
        'personal_domain_access',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration' => 'integer',
        'contract_limit' => 'integer',
        'elite_skill_access' => 'boolean',
        'personal_domain_access' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
