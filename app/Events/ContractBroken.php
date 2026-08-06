<?php

namespace App\Events;

use App\Models\SystemContract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractBroken
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SystemContract $contract;

    /**
     * Create a new event instance.
     */
    public function __construct(SystemContract $contract)
    {
        $this->contract = $contract;
    }
}
