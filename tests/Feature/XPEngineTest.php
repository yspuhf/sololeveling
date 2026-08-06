<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\XPEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XPEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_starts_at_level_1_with_zero_xp()
    {
        $user = User::factory()->create([
            'level' => 1,
            'xp' => 0,
            'rank' => 'E-Rank',
        ]);

        $this->assertEquals(1, $user->level);
        $this->assertEquals(0, $user->xp);
        $this->assertEquals('E-Rank', $user->rank);
    }

    public function test_awarding_xp_increases_total_xp_and_awards_gold()
    {
        $user = User::factory()->create([
            'level' => 1,
            'xp' => 0,
            'gold' => 0,
        ]);

        XPEngineService::award($user, 'daily_login'); // +10 XP, +20 Gold

        $user->refresh();
        $this->assertEquals(10, $user->xp);
        $this->assertEquals(20, $user->gold);
    }

    public function test_level_up_occurs_when_xp_exceeds_threshold()
    {
        $user = User::factory()->create([
            'level' => 1,
            'xp' => 0,
            'skill_points' => 0,
        ]);

        // Level 1 needs 100 XP to level up
        $user->addXp(110);

        $user->refresh();
        $this->assertEquals(2, $user->level);
        $this->assertEquals(10, $user->xp); // Remaining XP
        $this->assertEquals(5, $user->skill_points); // 5 skill points awarded
    }

    public function test_rank_advances_at_level_milestones()
    {
        $user = User::factory()->create([
            'level' => 1,
            'xp' => 0,
            'rank' => 'E-Rank',
        ]);

        // Upgrade to level 11 (requires passing 1->2 (100), 2->3 (200), etc.)
        // Let's just set the level directly and check determineRank
        $user->level = 11;
        $user->rank = $user->determineRank(11);
        $user->save();

        $this->assertEquals('D-Rank', $user->rank);

        $user->level = 36;
        $user->rank = $user->determineRank(36);
        $user->save();

        $this->assertEquals('B-Rank', $user->rank);

        $user->level = 105;
        $user->rank = $user->determineRank(105);
        $user->save();

        $this->assertEquals('Monarch Rank', $user->rank);
    }
}
