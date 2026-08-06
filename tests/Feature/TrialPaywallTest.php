<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemContract;
use App\Models\EliteSkill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TrialPaywallTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_initializes_trials_when_dashboard_first_mounted()
    {
        $user = User::factory()->create([
            'contracts_trial_started_at' => null,
            'domains_trial_started_at' => null,
            'skills_trial_started_at' => null,
        ]);

        $this->actingAs($user);

        Volt::test('hunter-dashboard');

        $user->refresh();
        $this->assertNotNull($user->contracts_trial_started_at);
        $this->assertNotNull($user->domains_trial_started_at);
        $this->assertNotNull($user->skills_trial_started_at);
    }

    public function test_contracts_locked_after_seven_days_and_unlocked_on_payment()
    {
        $user = User::factory()->create([
            'contracts_trial_started_at' => Carbon::now()->subDays(8),
            'is_contracts_paid' => false,
            'gold' => 0,
            'xp' => 0,
        ]);

        $this->actingAs($user);

        $component = Volt::test('hunter-dashboard');

        $component->assertSet('contractsTrialExpired', true);
        $component->assertSet('contractsDaysLeft', 0);

        // Attempting to check in should fail due to lock
        $component->call('checkIn');
        $component->assertSee('System Contracts are locked. Upgrade to unlock.');

        // Attempting to accept a contract should fail due to lock
        $component->call('acceptContract');
        $component->assertSee('System Contracts are locked. Upgrade to unlock.');

        // Pay for contracts
        $component->call('payForContracts');

        $user->refresh();
        $this->assertTrue($user->is_contracts_paid);
        $this->assertEquals(700, $user->gold);
        $this->assertEquals(2, $user->level);
        $this->assertEquals(0, $user->xp);

        $component->assertSet('contractsTrialExpired', false);
    }

    public function test_domains_locked_after_three_days_and_unlocked_on_payment()
    {
        $user = User::factory()->create([
            'domains_trial_started_at' => Carbon::now()->subDays(4),
            'is_domains_paid' => false,
            'gold' => 0,
            'xp' => 0,
        ]);

        $this->actingAs($user);

        $component = Volt::test('hunter-dashboard');

        $component->assertSet('domainsTrialExpired', true);
        $component->assertSet('domainsDaysLeft', 0);

        // Attempting to save domains should fail due to lock
        $component->call('saveDomains');
        $component->assertSee('Life Domain Scorecard is locked. Upgrade to unlock.');

        // Pay for domains
        $component->call('payForDomains');

        $user->refresh();
        $this->assertTrue($user->is_domains_paid);
        $this->assertEquals(1200, $user->gold);
        $this->assertEquals(2, $user->level);
        $this->assertEquals(0, $user->xp);

        $component->assertSet('domainsTrialExpired', false);
    }

    public function test_skills_locked_after_three_days_and_unlocked_on_payment()
    {
        $user = User::factory()->create([
            'skills_trial_started_at' => Carbon::now()->subDays(4),
            'is_skills_paid' => false,
            'gold' => 0,
            'xp' => 0,
            'skill_points' => 5,
        ]);

        $this->actingAs($user);

        $component = Volt::test('hunter-dashboard');

        $component->assertSet('skillsTrialExpired', true);
        $component->assertSet('skillsDaysLeft', 0);

        // Fetch a skill ID
        $skill = EliteSkill::where('user_id', $user->id)->first();
        $this->assertNotNull($skill);

        // Attempting to upgrade should fail due to lock
        $component->call('spendSkillPoint', $skill->id);
        $component->assertSee('Elite System Skills are locked. Upgrade to unlock.');

        // Pay for skills
        $component->call('payForSkills');

        $user->refresh();
        $this->assertTrue($user->is_skills_paid);
        $this->assertEquals(1700, $user->gold);
        $this->assertEquals(2, $user->level);
        $this->assertEquals(0, $user->xp);

        $component->assertSet('skillsTrialExpired', false);
    }
}
