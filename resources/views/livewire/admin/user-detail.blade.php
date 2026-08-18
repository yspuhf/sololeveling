<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\UserFeatureOverride;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $userId;
    public $user;
    public $status;
    
    // RPG Overrides
    public $xpAmount;
    public $goldAmount;

    // Feature toggles state
    public $hasContractsOverride;
    public $hasDomainsOverride;
    public $hasSkillsOverride;
    public $overrideReason = '';

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->loadUser();
    }

    public function loadUser()
    {
        $this->user = User::findOrFail($this->userId);
        $this->status = $this->user->status;
        $this->xpAmount = $this->user->xp;
        $this->goldAmount = $this->user->gold;

        // Load current overrides
        $this->hasContractsOverride = UserFeatureOverride::where('user_id', $this->userId)
            ->where('feature_key', 'contracts')
            ->value('enabled') ?? false;

        $this->hasDomainsOverride = UserFeatureOverride::where('user_id', $this->userId)
            ->where('feature_key', 'domains')
            ->value('enabled') ?? false;

        $this->hasSkillsOverride = UserFeatureOverride::where('user_id', $this->userId)
            ->where('feature_key', 'skills')
            ->value('enabled') ?? false;
    }

    public function updateStatus()
    {
        $oldStatus = $this->user->status;
        $this->user->status = $this->status;
        $this->user->save();

        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'update_user_status',
            'target_type' => 'User',
            'target_id' => $this->user->id,
            'old_value' => $oldStatus,
            'new_value' => $this->status,
            'reason' => 'Admin status change manual update',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Hunter status updated successfully!');
        $this->loadUser();
    }

    public function updateRpgStats()
    {
        $oldXp = $this->user->xp;
        $oldGold = $this->user->gold;

        $this->user->xp = (int) $this->xpAmount;
        $this->user->gold = (int) $this->goldAmount;
        $this->user->save();

        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'update_rpg_stats',
            'target_type' => 'User',
            'target_id' => $this->user->id,
            'old_value' => json_encode(['xp' => $oldXp, 'gold' => $oldGold]),
            'new_value' => json_encode(['xp' => $this->xpAmount, 'gold' => $this->goldAmount]),
            'reason' => 'Admin adjustment of XP/Gold',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Hunter RPG stats successfully adjusted!');
        $this->loadUser();
    }

    public function saveOverrides()
    {
        $features = [
            'contracts' => $this->hasContractsOverride,
            'domains' => $this->hasDomainsOverride,
            'skills' => $this->hasSkillsOverride,
        ];

        foreach ($features as $key => $enabled) {
            $oldOverride = UserFeatureOverride::where('user_id', $this->userId)
                ->where('feature_key', $key)
                ->first();

            $oldVal = $oldOverride ? (bool)$oldOverride->enabled : null;

            if ($oldOverride) {
                if ($oldVal !== (bool)$enabled) {
                    $oldOverride->update([
                        'enabled' => $enabled,
                        'reason' => $this->overrideReason,
                        'created_by' => Auth::id(),
                    ]);
                    $this->logOverrideChange($key, $oldVal, $enabled);
                }
            } else {
                UserFeatureOverride::create([
                    'user_id' => $this->userId,
                    'feature_key' => $key,
                    'enabled' => $enabled,
                    'reason' => $this->overrideReason,
                    'created_by' => Auth::id(),
                ]);
                $this->logOverrideChange($key, null, $enabled);
            }
        }

        session()->flash('success', 'Feature overrides saved successfully!');
        $this->overrideReason = '';
        $this->loadUser();
    }

    protected function logOverrideChange($feature, $oldVal, $newVal)
    {
        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'update_feature_override',
            'target_type' => 'UserFeatureOverride',
            'target_id' => $this->user->id,
            'old_value' => $oldVal === null ? 'none' : ($oldVal ? 'enabled' : 'disabled'),
            'new_value' => $newVal ? 'enabled' : 'disabled',
            'reason' => 'Feature: ' . $feature . ' | Reason: ' . ($this->overrideReason ?: 'Admin override'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}; ?>

<div class="space-y-8">
    
    <!-- Top back navigation link -->
    <div>
        <a href="{{ route('admin.users') }}" class="text-xs text-slate-400 hover:text-white font-title font-bold tracking-widest flex items-center gap-1.5 transition">
            ⬅️ RETURN TO HUNTERS ROSTER
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Profile summary & status -->
        <div class="space-y-6 lg:col-span-1">
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(139,92,246,0.01)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
                <div class="space-y-2 text-center pb-6 border-b border-white/5">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-neon-blue via-neon-purple to-pink-500 flex items-center justify-center font-bold text-obsidian-dark font-title text-3xl tracking-wider shadow-lg mx-auto uppercase">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="font-title text-base font-black text-white tracking-widest uppercase">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500 font-mono">{{ $user->email }}</p>
                    <div class="pt-2">
                        @php $rank = $user->determineRank(); @endphp
                        <span class="px-3 py-1 rounded-md text-[10px] font-title font-black tracking-widest uppercase bg-neon-purple/10 text-neon-purple border border-neon-purple/20">
                            {{ $rank }} Hunter
                        </span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500 font-title font-bold">LEVEL</span>
                        <span class="text-white font-title font-black">{{ $user->level }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500 font-title font-bold">CURRENT XP</span>
                        <span class="text-white font-mono">{{ $user->xp }} XP</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500 font-title font-bold">CURRENT GOLD</span>
                        <span class="text-gold-rpg font-title font-black">{{ $user->gold }} GOLD</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500 font-title font-bold">JOINED DATE</span>
                        <span class="text-slate-300 font-mono">{{ $user->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>

                <!-- Account Status modifier form -->
                <form wire:submit="updateStatus" class="space-y-3 pt-6 border-t border-white/5">
                    <div>
                        <x-input-label for="status" :value="__('ACCOUNT GATEWAY STATUS')" />
                        <select wire:model="status" class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition">
                            <option value="active">Active</option>
                            <option value="pending_verification">Pending Verification</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-white/5 border border-white/10 hover:border-white/20 text-white font-title font-bold text-xs tracking-widest rounded-xl transition duration-300">
                        CONFIRM STATUS CHANGE
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Admin overrides, edit stats, logs -->
        <div class="space-y-6 lg:col-span-2">
            
            <!-- Feature Overrides Controls -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6">
                <div>
                    <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">💎 PREMIUM GATEWAY OVERRIDES</h3>
                    <p class="text-xs text-slate-500 mt-1">Directly authorize or revoke specific access scopes for this hunter account.</p>
                </div>

                <form wire:submit="saveOverrides" class="space-y-6">
                    <div class="space-y-4">
                        <!-- Contracts Override -->
                        <div class="flex items-center justify-between p-4 bg-black/30 border border-white/5 rounded-xl">
                            <div class="space-y-1">
                                <span class="text-xs font-title font-bold text-white block">SYSTEM CONTRACTS TRIAL BYPASS</span>
                                <span class="text-[10px] text-slate-500 block">Authorize duration contracts exceeding the standard 7-day restriction.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="hasContractsOverride" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-neon-purple"></div>
                            </label>
                        </div>

                        <!-- Domains Override -->
                        <div class="flex items-center justify-between p-4 bg-black/30 border border-white/5 rounded-xl">
                            <div class="space-y-1">
                                <span class="text-xs font-title font-bold text-white block">PERSONAL DOMAIN RE-EVALUATION</span>
                                <span class="text-[10px] text-slate-500 block">Allow the user to evaluate and modify personal metrics indefinitely.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="hasDomainsOverride" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-neon-purple"></div>
                            </label>
                        </div>

                        <!-- Skills Override -->
                        <div class="flex items-center justify-between p-4 bg-black/30 border border-white/5 rounded-xl">
                            <div class="space-y-1">
                                <span class="text-xs font-title font-bold text-white block">ELITE S-RANK SKILLS AUTHORIZATION</span>
                                <span class="text-[10px] text-slate-500 block">Unlock S-Rank skill awakenings and level enhancements.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="hasSkillsOverride" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-neon-purple"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Audit trail input validation -->
                    <div class="space-y-2">
                        <x-input-label for="overrideReason" :value="__('JUSTIFICATION REASON (REQUIRED FOR AUDIT LOG)')" />
                        <input 
                            type="text" 
                            id="overrideReason"
                            required
                            placeholder="State reason for overriding entitlements..." 
                            wire:model="overrideReason" 
                            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
                        >
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-neon-purple to-pink-500 text-white font-title font-black text-xs tracking-widest rounded-xl transition duration-300 hover:opacity-90 shadow-neon-purple/20">
                            APPLY OVERRIDES
                        </button>
                    </div>
                </form>
            </div>

            <!-- RPG stats modifiers -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6">
                <div>
                    <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">🛡️ RPG METRIC ENHANCEMENT</h3>
                    <p class="text-xs text-slate-500 mt-1">Manual correction database adjustments for XP and Gold values.</p>
                </div>

                <form wire:submit="updateRpgStats" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="xpAmount" :value="__('SET XP AMOUNT')" />
                        <input 
                            type="number" 
                            id="xpAmount"
                            wire:model="xpAmount" 
                            class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition"
                        >
                    </div>
                    <div>
                        <x-input-label for="goldAmount" :value="__('SET GOLD AMOUNT')" />
                        <input 
                            type="number" 
                            id="goldAmount"
                            wire:model="goldAmount" 
                            class="w-full mt-1 bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-neon-purple focus:ring-0 transition"
                        >
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-white/5 border border-white/10 hover:border-white/20 text-white font-title font-bold text-xs tracking-widest rounded-xl transition duration-300">
                            UPDATE RPG STATS
                        </button>
                    </div>
                </form>
            </div>

            <!-- Audit trail history for this user -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-4">
                <div>
                    <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">📜 HISTORICAL AUDIT TRAILS</h3>
                    <p class="text-xs text-slate-500 mt-1">Audit log records of administrator adjustments for this hunter.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                                <th class="p-3">DATE</th>
                                <th class="p-3">ADMINISTRATOR</th>
                                <th class="p-3">ACTION</th>
                                <th class="p-3">JUSTIFICATION / DETAILS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse(App\Models\AdminAuditLog::where(function($query) {
                                $query->where('target_type', 'User')->where('target_id', $this->userId);
                            })->orWhere(function($query) {
                                $query->where('target_type', 'UserFeatureOverride')->where('target_id', $this->userId);
                            })->orderBy('created_at', 'desc')->get() as $log)
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-3 font-mono text-slate-400">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-3 font-bold">{{ $log->admin->name ?? 'System' }}</td>
                                    <td class="p-3 font-mono text-neon-blue uppercase">{{ $log->action }}</td>
                                    <td class="p-3 text-slate-400">
                                        <div class="max-w-xs truncate" title="{{ $log->reason }}">{{ $log->reason ?: 'No detail' }}</div>
                                        <div class="text-[9px] text-slate-600 font-mono">
                                            Val: {{ $log->old_value }} ➡️ {{ $log->new_value }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-slate-500 font-title font-bold tracking-wider">
                                        NO AUDIT LOG ENTRIES RECORDED FOR THIS HUNTER
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
