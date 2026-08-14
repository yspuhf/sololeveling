<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'level',
        'xp',
        'gold',
        'skill_points',
        'current_streak',
        'highest_streak',
        'rank',
        'contracts_trial_started_at',
        'domains_trial_started_at',
        'skills_trial_started_at',
        'is_contracts_paid',
        'is_domains_paid',
        'is_skills_paid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'level' => 'integer',
            'xp' => 'integer',
            'gold' => 'integer',
            'skill_points' => 'integer',
            'current_streak' => 'integer',
            'highest_streak' => 'integer',
            'contracts_trial_started_at' => 'datetime',
            'domains_trial_started_at' => 'datetime',
            'skills_trial_started_at' => 'datetime',
            'is_contracts_paid' => 'boolean',
            'is_domains_paid' => 'boolean',
            'is_skills_paid' => 'boolean',
        ];
    }

    /*
     * RPG & Gamification Relationships
     */

    public function systemContracts(): HasMany
    {
        return $this->hasMany(SystemContract::class);
    }

    public function lifeDomain(): HasOne
    {
        return $this->hasOne(LifeDomain::class);
    }

    public function eliteSkills(): HasMany
    {
        return $this->hasMany(EliteSkill::class);
    }

    public function managedGuilds(): HasMany
    {
        return $this->hasMany(Guild::class, 'master_id');
    }

    public function guilds(): BelongsToMany
    {
        return $this->belongsToMany(Guild::class, 'guild_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function dailyQuests(): HasMany
    {
        return $this->hasMany(DailyQuest::class);
    }

    /*
     * RPG Progression Logic
     */

    public function addXp(int $amount): void
    {
        $this->xp += $amount;
        $this->save();

        $this->checkLevelUp();
    }

    public function checkLevelUp(): void
    {
        $originalLevel = $this->level;

        // Level-up logic: next level requires current_level * 100 XP
        while ($this->xp >= ($this->level * 100)) {
            $this->xp -= ($this->level * 100);
            $this->level++;
            $this->skill_points += 5; // Reward skill points per level
        }

        if ($this->level !== $originalLevel) {
            $this->rank = $this->determineRank($this->level);
            $this->save();

            // Fire leveling event
            event(new \App\Events\UserLeveledUp($this, $originalLevel, $this->level));
        }
    }

    public function determineRank(int $level): string
    {
        if ($level <= 10) return 'E-Rank';
        if ($level <= 20) return 'D-Rank';
        if ($level <= 35) return 'C-Rank';
        if ($level <= 50) return 'B-Rank';
        if ($level <= 70) return 'A-Rank';
        if ($level <= 90) return 'S-Rank';
        if ($level <= 99) return 'National Rank';
        return 'Monarch Rank';
    }

    /**
     * Send the custom email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /*
     * Admin & RBAC Roles
     */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function isAdmin(): bool
    {
        return $this->roles()->where('name', 'super_admin')->exists();
    }

    /*
     * Subscriptions & Payments
     */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(UserFeatureOverride::class);
    }

    public function hasContractsAccess(): bool
    {
        return \App\Services\FeatureEntitlementService::check($this, 'contracts');
    }

    public function hasDomainsAccess(): bool
    {
        return \App\Services\FeatureEntitlementService::check($this, 'domains');
    }

    public function hasSkillsAccess(): bool
    {
        return \App\Services\FeatureEntitlementService::check($this, 'skills');
    }
}

