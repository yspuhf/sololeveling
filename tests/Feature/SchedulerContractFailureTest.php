<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemContract;
use App\Models\ContractCheckin;
use App\Events\ContractBroken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SchedulerContractFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_fails_contract_when_day_checkin_is_missed()
    {
        Event::fake([ContractBroken::class]);

        $user = User::factory()->create([
            'current_streak' => 5,
        ]);

        // Start contract yesterday (so today is day 2)
        $startDate = Carbon::yesterday();
        $contract = SystemContract::create([
            'user_id' => $user->id,
            'title' => 'Streak Test',
            'duration_days' => 7,
            'difficulty' => 'Easy',
            'xp_reward' => 100,
            'gold_reward' => 200,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays(6),
        ]);

        // Day 1 was completed, Day 2 (today) check-in is NOT checked (is_checked = false)
        $contract->checkins()->where('day_number', 1)->update(['is_checked' => true, 'completed_at' => Carbon::yesterday()]);
        
        // Execute failure artisan command
        $this->artisan('arise:contracts:check-failures')
            ->expectsOutput('Scanning active system contracts for missed check-ins...')
            ->assertExitCode(0);

        $contract->refresh();
        $user->refresh();

        // Contract must be failed
        $this->assertEquals('failed', $contract->status);
        $this->assertNotNull($contract->failed_at);

        // Streak must be reset
        $this->assertEquals(0, $user->current_streak);

        // Event should be fired
        Event::assertDispatched(ContractBroken::class, function ($event) use ($contract) {
            return $event->contract->id === $contract->id;
        });
    }

    public function test_scheduler_does_not_fail_contract_when_today_is_checked_in()
    {
        Event::fake([ContractBroken::class]);

        $user = User::factory()->create([
            'current_streak' => 5,
        ]);

        $startDate = Carbon::today();
        $contract = SystemContract::create([
            'user_id' => $user->id,
            'title' => 'Daily coding',
            'duration_days' => 7,
            'difficulty' => 'Easy',
            'xp_reward' => 100,
            'gold_reward' => 200,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays(6),
        ]);

        // Complete check-in for Day 1 (today)
        $contract->checkins()->where('day_number', 1)->update(['is_checked' => true, 'completed_at' => Carbon::now()]);

        // Execute command
        $this->artisan('arise:contracts:check-failures')
            ->assertExitCode(0);

        $contract->refresh();
        $user->refresh();

        // Contract remains active
        $this->assertEquals('active', $contract->status);
        $this->assertNull($contract->failed_at);

        // Streak remains unchanged
        $this->assertEquals(5, $user->current_streak);

        // Event should NOT be fired
        Event::assertNotDispatched(ContractBroken::class);
    }
}

