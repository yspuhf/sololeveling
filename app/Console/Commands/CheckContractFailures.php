<?php

namespace App\Console\Commands;

use App\Models\SystemContract;
use App\Events\ContractBroken;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckContractFailures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arise:contracts:check-failures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans active system contracts and marks them failed if the current day check-in was missed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning active system contracts for missed check-ins...');

        $activeContracts = SystemContract::where('status', 'active')->get();
        $failedCount = 0;

        foreach ($activeContracts as $contract) {
            $start = Carbon::parse($contract->start_date)->startOfDay();
            $now = Carbon::now()->startOfDay();
            $dayNumber = $start->diffInDays($now) + 1;

            $this->info("DEBUG: Contract ID {$contract->id} | Start: {$start->toDateString()} | Now: {$now->toDateString()} | Computed Day: {$dayNumber}");

            // If dayNumber is within the contract duration
            if ($dayNumber <= $contract->duration_days) {
                // Find check-in for the current day
                $checkin = $contract->checkins()->where('day_number', $dayNumber)->first();

                if ($checkin) {
                    $this->info("DEBUG: Checkin found for Day {$dayNumber} | Is Checked: " . ($checkin->is_checked ? 'TRUE' : 'FALSE'));
                } else {
                    $this->info("DEBUG: Checkin NOT found for Day {$dayNumber}");
                }

                // If checkin doesn't exist or is not checked, the contract is broken!
                if (!$checkin || !$checkin->is_checked) {
                    $contract->status = 'failed';
                    $contract->failed_at = Carbon::now();
                    $contract->save();

                    // Reset user streak
                    $user = $contract->user;
                    $user->current_streak = 0;
                    $user->save();

                    // Fire broken event
                    event(new ContractBroken($contract));

                    $failedCount++;
                    $this->warn("Contract ID {$contract->id} for user {$user->name} marked as FAILED.");
                }
            } else {
                // If it exceeded duration but was somehow not marked completed, mark it failed or clean it up
                $contract->status = 'failed';
                $contract->failed_at = Carbon::now();
                $contract->save();

                $user = $contract->user;
                $user->current_streak = 0;
                $user->save();

                event(new ContractBroken($contract));
                $failedCount++;
            }
        }

        $this->info("Scan complete. {$failedCount} contracts failed.");
    }
}
