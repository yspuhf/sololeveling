<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemContract;
use App\Events\ContractBroken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ActiveContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_up_to_five_active_system_contracts()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Pre-create 4 active contracts
        for ($i = 1; $i <= 4; $i++) {
            SystemContract::create([
                'user_id' => $user->id,
                'title' => "Contract {$i}",
                'duration_days' => 7,
                'difficulty' => 'Easy',
                'xp_reward' => 100,
                'gold_reward' => 200,
                'status' => 'active',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDays(6),
            ]);
        }

        $component = Volt::test('hunter-dashboard')
            ->set('newContractTitle', 'Fifth Contract')
            ->set('newContractDuration', 7)
            ->set('newContractDifficulty', 'Easy')
            ->call('acceptContract');

        // Verify the 5th contract was successfully created
        $this->assertEquals(5, $user->systemContracts()->where('status', 'active')->count());

        // Now attempt to add a 6th contract
        $component->set('newContractTitle', 'Sixth Contract')
            ->call('acceptContract');

        // It should output validation warning and block creation
        $component->assertSee('You have reached the maximum limit of 5 active system contracts.');
        $this->assertEquals(5, $user->systemContracts()->where('status', 'active')->count());
    }

    public function test_dashboard_mount_dynamically_fails_contract_with_prior_missed_checkin()
    {
        Event::fake([ContractBroken::class]);

        $user = User::factory()->create([
            'current_streak' => 10,
        ]);
        $this->actingAs($user);

        // Start contract 3 days ago (so today is Day 4)
        $startDate = Carbon::today()->subDays(3);
        $contract = SystemContract::create([
            'user_id' => $user->id,
            'title' => 'Consequent Test',
            'duration_days' => 7,
            'difficulty' => 'Easy',
            'xp_reward' => 100,
            'gold_reward' => 200,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays(6),
        ]);

        // Day 1 was completed, Day 2 was NOT completed (missed), Day 3 was completed
        $contract->checkins()->where('day_number', 1)->update(['is_checked' => true, 'completed_at' => $startDate]);
        $contract->checkins()->where('day_number', 2)->update(['is_checked' => false]);
        $contract->checkins()->where('day_number', 3)->update(['is_checked' => true, 'completed_at' => $startDate->copy()->addDays(2)]);

        // Mount the dashboard - this should trigger loadDashboardData() and fail the contract
        $component = Volt::test('hunter-dashboard');

        $contract->refresh();
        $user->refresh();

        // The contract should be failed
        $this->assertEquals('failed', $contract->status);
        $this->assertNotNull($contract->failed_at);

        // User streak should be reset to 0
        $this->assertEquals(0, $user->current_streak);

        // Warning session flash should have fired
        $component->assertSee("MISSION FAILED: System Contract");
        $component->assertSee("has failed due to missed check-in on Day 2! Streaks reset to 0.");
        
        // Event should be dispatched
        Event::assertDispatched(ContractBroken::class);
    }

    public function test_checkin_fails_instantly_on_prior_missed_days()
    {
        Event::fake([ContractBroken::class]);

        $user = User::factory()->create([
            'current_streak' => 8,
        ]);
        $this->actingAs($user);

        // Mount the dashboard first (so mount doesn't clean the contract yet)
        $component = Volt::test('hunter-dashboard');

        // Start contract 2 days ago (so today is Day 3)
        $startDate = Carbon::today()->subDays(2);
        $contract = SystemContract::create([
            'user_id' => $user->id,
            'title' => 'Checkin Guard Test',
            'duration_days' => 7,
            'difficulty' => 'Easy',
            'xp_reward' => 100,
            'gold_reward' => 200,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays(6),
        ]);

        // Day 1 completed, Day 2 missed.
        $contract->checkins()->where('day_number', 1)->update(['is_checked' => true, 'completed_at' => $startDate]);
        $contract->checkins()->where('day_number', 2)->update(['is_checked' => false]);
        
        $component->call('checkIn', $contract->id);

        $contract->refresh();
        $user->refresh();

        // Should fail and reset streak
        $this->assertEquals('failed', $contract->status);
        $this->assertEquals(0, $user->current_streak);
        $component->assertSee("MISSION FAILED: System Contract");
        $component->assertSee("has failed due to missed check-in on Day 2! Streaks reset to 0.");
        Event::assertDispatched(ContractBroken::class);
    }

    public function test_trial_user_cannot_create_non_seven_day_contract_unless_paid()
    {
        $user = User::factory()->create([
            'is_contracts_paid' => false,
        ]);
        $this->actingAs($user);

        // Attempt to create a 21-day contract as a trial user
        $component = Volt::test('hunter-dashboard')
            ->set('newContractTitle', 'Trial 21 Day Contract')
            ->set('newContractDuration', 21)
            ->set('newContractDifficulty', 'Easy')
            ->call('acceptContract');

        // It should assert that no contract was created
        $this->assertEquals(0, $user->systemContracts()->count());
        $component->assertSee('Durations longer than 7 days require a National Rank upgrade (Rs 99).');

        // Upgrade to paid status
        $component->call('payForContracts');
        $user->refresh();
        $this->assertTrue((bool)$user->is_contracts_paid);

        // Attempt to create the 21-day contract again
        $component->set('newContractTitle', 'Paid 21 Day Contract')
            ->set('newContractDuration', 21)
            ->set('newContractDifficulty', 'Easy')
            ->call('acceptContract');

        // It should successfully create the contract
        $this->assertEquals(1, $user->systemContracts()->count());
        $contract = $user->systemContracts()->first();
        $this->assertEquals(21, $contract->duration_days);
    }
}
