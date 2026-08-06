<?php

namespace App\Services;

use App\Models\User;

class XPEngineService
{
    public const SOURCES = [
        'daily_login' => 10,
        'daily_quest' => 25,
        'skill_challenge' => 50,
        'achievement' => 100,
        'life_domain_milestone' => 250,
        'contract_completion' => 500,
    ];

    /**
     * Award XP to a user based on a pre-defined gamified event.
     */
    public static function award(User $user, string $source): void
    {
        $xpAmount = self::SOURCES[$source] ?? 0;
        if ($xpAmount > 0) {
            $user->addXp($xpAmount);
            // Award gold proportional to the XP gained to complete the RPG economy
            $user->gold += $xpAmount * 2;
            $user->save();
        }
    }
}
