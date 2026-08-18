<?php

use Livewire\Volt\Component;
use App\Models\Plan;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $plans;
    public $editingPlanId = null;
    public $editName;
    public $editPrice;
    public $editDuration;
    public $editContractLimit;
    public $editEliteSkillAccess;
    public $editPersonalDomainAccess;

    public function mount()
    {
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $this->plans = Plan::all();
    }

    public function editPlan($id)
    {
        $plan = Plan::findOrFail($id);
        $this->editingPlanId = $plan->id;
        $this->editName = $plan->name;
        $this->editPrice = $plan->price;
        $this->editDuration = $plan->duration;
        $this->editContractLimit = $plan->contract_limit;
        $this->editEliteSkillAccess = $plan->elite_skill_access;
        $this->editPersonalDomainAccess = $plan->personal_domain_access;
    }

    public function savePlan()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editPrice' => 'required|integer|min:0',
            'editDuration' => 'required|integer|min:0',
            'editContractLimit' => 'required|integer|min:0',
        ]);

        $plan = Plan::findOrFail($this->editingPlanId);
        $oldData = $plan->toArray();

        $plan->update([
            'name' => $this->editName,
            'price' => (int) $this->editPrice,
            'duration' => (int) $this->editDuration,
            'contract_limit' => (int) $this->editContractLimit,
            'elite_skill_access' => (bool) $this->editEliteSkillAccess,
            'personal_domain_access' => (bool) $this->editPersonalDomainAccess,
        ]);

        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'update_membership_plan',
            'target_type' => 'Plan',
            'target_id' => $plan->id,
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($plan->toArray()),
            'reason' => 'Admin edited membership plan parameters',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Plan [' . $plan->name . '] updated successfully!');
        $this->editingPlanId = null;
        $this->loadPlans();
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg">
        <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">💎 MEMBERSHIP PLANS CONFIGURATION</h3>
        <p class="text-xs text-slate-500 mt-1">Configure pricing tiers, active durations, contract capacity limits, and premium access privileges.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Editing Panel overlay -->
    @if ($editingPlanId !== null)
        <div class="bg-obsidian-card border border-neon-purple/20 rounded-2xl p-6 shadow-lg space-y-4">
            <h4 class="font-title text-xs font-black text-white tracking-widest uppercase">EDIT TIERS: {{ strtoupper($editName) }}</h4>
            <form wire:submit="savePlan" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="editPrice" :value="__('PRICE (INR)')" />
                        <input type="number" id="editPrice" wire:model="editPrice" class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition">
                    </div>
                    <div>
                        <x-input-label for="editDuration" :value="__('ACTIVE DURATION (DAYS)')" />
                        <input type="number" id="editDuration" wire:model="editDuration" class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition">
                    </div>
                    <div>
                        <x-input-label for="editContractLimit" :value="__('CONTRACT LIMIT')" />
                        <input type="number" id="editContractLimit" wire:model="editContractLimit" class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition">
                    </div>
                    <div class="flex items-center gap-6 pt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="editEliteSkillAccess" class="rounded border-white/20 bg-black/40 text-neon-purple focus:ring-0">
                            <span class="ms-2 text-xs text-slate-400 font-title font-bold tracking-wider">ELITE SKILLS</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="editPersonalDomainAccess" class="rounded border-white/20 bg-black/40 text-neon-purple focus:ring-0">
                            <span class="ms-2 text-xs text-slate-400 font-title font-bold tracking-wider">PERSONAL DOMAINS</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="$set('editingPlanId', null)" class="px-4 py-2 border border-white/10 text-gray-400 font-title font-bold text-xs rounded-xl hover:border-white/20">
                        ABANDON
                    </button>
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-neon-purple to-pink-500 text-white font-title font-black text-xs tracking-widest rounded-xl transition duration-300 shadow-neon-purple/20">
                        SAVE TIERS
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Plans Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4">NAME</th>
                        <th class="p-4">PRICE</th>
                        <th class="p-4">DURATION</th>
                        <th class="p-4">CONTRACT LIMIT</th>
                        <th class="p-4">ELITE SKILLS</th>
                        <th class="p-4">PERSONAL DOMAINS</th>
                        <th class="p-4 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @foreach($plans as $plan)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-title font-black text-white uppercase">{{ $plan->name }}</td>
                            <td class="p-4 font-mono font-bold text-green-400">₹{{ number_format($plan->price) }}</td>
                            <td class="p-4 font-mono text-slate-400">{{ $plan->duration }} Days</td>
                            <td class="p-4 font-mono">{{ $plan->contract_limit }} Capacity</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase {{ $plan->elite_skill_access ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $plan->elite_skill_access ? 'AUTHORIZED' : 'LOCKED' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase {{ $plan->personal_domain_access ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $plan->personal_domain_access ? 'AUTHORIZED' : 'LOCKED' }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <button wire:click="editPlan({{ $plan->id }})" class="px-3.5 py-1.5 bg-neon-purple/10 hover:bg-neon-purple/20 text-white font-title font-bold text-[10px] tracking-wider rounded-lg transition border border-neon-purple/20">
                                    MODIFY
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
