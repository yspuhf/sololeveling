<?php

use Livewire\Volt\Component;
use App\Models\FeatureFlag;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $registrationEnabled;
    public $contractsEnabled;
    public $skillsEnabled;
    public $domainsEnabled;

    public function mount()
    {
        $this->loadFlags();
    }

    public function loadFlags()
    {
        $this->registrationEnabled = FeatureFlag::where('feature_key', 'registration')->value('enabled') ?? true;
        $this->contractsEnabled = FeatureFlag::where('feature_key', 'contracts')->value('enabled') ?? true;
        $this->skillsEnabled = FeatureFlag::where('feature_key', 'skills')->value('enabled') ?? true;
        $this->domainsEnabled = FeatureFlag::where('feature_key', 'domains')->value('enabled') ?? true;
    }

    public function toggleFlag($key)
    {
        $flag = FeatureFlag::firstOrCreate(['feature_key' => $key]);
        $oldVal = (bool) $flag->enabled;
        $newVal = !$oldVal;

        $flag->enabled = $newVal;
        $flag->save();

        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'toggle_global_feature',
            'target_type' => 'FeatureFlag',
            'target_id' => $flag->id,
            'old_value' => $oldVal ? 'enabled' : 'disabled',
            'new_value' => $newVal ? 'enabled' : 'disabled',
            'reason' => 'Global control toggle action',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Global flag [' . strtoupper($key) . '] toggled successfully!');
        $this->loadFlags();
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg">
        <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">⚙️ GLOBAL FEATURE GATEWAY CONTROLS</h3>
        <p class="text-xs text-slate-500 mt-1">Suspend or authorize core system features globally. Changes affect all hunters instantly.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Toggles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Registration Toggle -->
        <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg flex flex-col justify-between space-y-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $registrationEnabled ? 'bg-green-400' : 'bg-red-500' }}"></span>
                    <h4 class="font-title text-xs font-black text-white tracking-widest uppercase">NEW USER REGISTRATION</h4>
                </div>
                <p class="text-xs text-slate-500">Allow new hunters to trigger register awakenings and create accounts.</p>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-white/5">
                <span class="text-xs font-mono font-bold {{ $registrationEnabled ? 'text-green-400' : 'text-red-500' }}">
                    {{ $registrationEnabled ? 'SYSTEM_GATEWAY: ACTIVE' : 'SYSTEM_GATEWAY: LOCKED' }}
                </span>
                <button 
                    wire:click="toggleFlag('registration')" 
                    class="px-4 py-2 text-xs font-title font-bold tracking-widest rounded-xl transition duration-300 {{ $registrationEnabled ? 'bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20' : 'bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20' }}"
                >
                    {{ $registrationEnabled ? 'LOCK REGISTRATION' : 'UNLOCK REGISTRATION' }}
                </button>
            </div>
        </div>

        <!-- Contracts Toggle -->
        <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg flex flex-col justify-between space-y-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $contractsEnabled ? 'bg-green-400' : 'bg-red-500' }}"></span>
                    <h4 class="font-title text-xs font-black text-white tracking-widest uppercase">CONTRACT CREATION</h4>
                </div>
                <p class="text-xs text-slate-500">Enforce habit timeline contracts. Disabling blocks all new contracts from being accepted.</p>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-white/5">
                <span class="text-xs font-mono font-bold {{ $contractsEnabled ? 'text-green-400' : 'text-red-500' }}">
                    {{ $contractsEnabled ? 'SYSTEM_GATEWAY: ACTIVE' : 'SYSTEM_GATEWAY: LOCKED' }}
                </span>
                <button 
                    wire:click="toggleFlag('contracts')" 
                    class="px-4 py-2 text-xs font-title font-bold tracking-widest rounded-xl transition duration-300 {{ $contractsEnabled ? 'bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20' : 'bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20' }}"
                >
                    {{ $contractsEnabled ? 'LOCK CONTRACTS' : 'UNLOCK CONTRACTS' }}
                </button>
            </div>
        </div>

        <!-- Elite Skills Toggle -->
        <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg flex flex-col justify-between space-y-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $skillsEnabled ? 'bg-green-400' : 'bg-red-500' }}"></span>
                    <h4 class="font-title text-xs font-black text-white tracking-widest uppercase">ELITE S-RANK SKILLS</h4>
                </div>
                <p class="text-xs text-slate-500">Control access to upgrades, awakenings, and level enhancements for skills.</p>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-white/5">
                <span class="text-xs font-mono font-bold {{ $skillsEnabled ? 'text-green-400' : 'text-red-500' }}">
                    {{ $skillsEnabled ? 'SYSTEM_GATEWAY: ACTIVE' : 'SYSTEM_GATEWAY: LOCKED' }}
                </span>
                <button 
                    wire:click="toggleFlag('skills')" 
                    class="px-4 py-2 text-xs font-title font-bold tracking-widest rounded-xl transition duration-300 {{ $skillsEnabled ? 'bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20' : 'bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20' }}"
                >
                    {{ $skillsEnabled ? 'LOCK ELITE SKILLS' : 'UNLOCK ELITE SKILLS' }}
                </button>
            </div>
        </div>

        <!-- Personal Domains Toggle -->
        <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg flex flex-col justify-between space-y-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $domainsEnabled ? 'bg-green-400' : 'bg-red-500' }}"></span>
                    <h4 class="font-title text-xs font-black text-white tracking-widest uppercase">PERSONAL LIFE DOMAINS</h4>
                </div>
                <p class="text-xs text-slate-500">Control domains scorecards and evaluation parameters modifications.</p>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-white/5">
                <span class="text-xs font-mono font-bold {{ $domainsEnabled ? 'text-green-400' : 'text-red-500' }}">
                    {{ $domainsEnabled ? 'SYSTEM_GATEWAY: ACTIVE' : 'SYSTEM_GATEWAY: LOCKED' }}
                </span>
                <button 
                    wire:click="toggleFlag('domains')" 
                    class="px-4 py-2 text-xs font-title font-bold tracking-widest rounded-xl transition duration-300 {{ $domainsEnabled ? 'bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20' : 'bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20' }}"
                >
                    {{ $domainsEnabled ? 'LOCK DOMAINS' : 'UNLOCK DOMAINS' }}
                </button>
            </div>
        </div>

    </div>
</div>
