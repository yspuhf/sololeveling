<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\SystemContract;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\FeatureEntitlementService;
use Illuminate\Support\Carbon;

new class extends Component {
    public $stats = [];
    public $todayStats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $now = Carbon::now();
        $todayStart = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // Basic counts
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        
        // Paid/Unpaid users
        $paidUsers = User::where('is_contracts_paid', true)
            ->orWhere('is_domains_paid', true)
            ->orWhere('is_skills_paid', true)
            ->orWhereHas('subscriptions', function ($q) {
                $q->where('status', 'active')->where('expires_at', '>', now());
            })
            ->distinct()
            ->count();
        $unpaidUsers = max(0, $totalUsers - $paidUsers);

        // Revenue
        $totalRevenue = Payment::where('status', 'successful')->sum('amount');

        // Contracts
        $totalContracts = SystemContract::count();
        $contractsToday = SystemContract::whereDate('created_at', $todayStart)->count();
        $contractsMonth = SystemContract::where('created_at', '>=', $monthStart)->count();

        // Feature adoption
        $eliteSkillsUsers = User::where('is_skills_paid', true)
            ->orWhereHas('featureOverrides', function ($q) {
                $q->where('feature_key', 'skills')->where('enabled', true);
            })
            ->orWhereHas('subscriptions.plan', function ($q) {
                $q->where('elite_skill_access', true);
            })
            ->distinct()
            ->count();

        $personalDomainUsers = User::where('is_domains_paid', true)
            ->orWhereHas('featureOverrides', function ($q) {
                $q->where('feature_key', 'domains')->where('enabled', true);
            })
            ->orWhereHas('subscriptions.plan', function ($q) {
                $q->where('personal_domain_access', true);
            })
            ->distinct()
            ->count();

        // Today's summary
        $newUsersToday = User::whereDate('created_at', $todayStart)->count();
        $paymentsToday = Payment::where('status', 'successful')->whereDate('created_at', $todayStart)->count();
        $revenueToday = Payment::where('status', 'successful')->whereDate('created_at', $todayStart)->sum('amount');

        $this->stats = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'paid_users' => $paidUsers,
            'unpaid_users' => $unpaidUsers,
            'total_revenue' => $totalRevenue,
            'total_contracts' => $totalContracts,
            'contracts_today' => $contractsToday,
            'contracts_month' => $contractsMonth,
            'elite_skills' => $eliteSkillsUsers,
            'personal_domain' => $personalDomainUsers,
        ];

        $this->todayStats = [
            'new_users' => $newUsersToday,
            'new_payments' => $paymentsToday,
            'revenue_today' => $revenueToday,
            'contracts_created' => $contractsToday,
        ];
    }
}; ?>

<div class="space-y-8">
    
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-obsidian-card to-[#120a21] border border-neon-purple/20 rounded-2xl p-6 md:p-8 relative overflow-hidden shadow-xl">
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(138,43,226,0.02)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <h1 class="font-title text-2xl font-black text-white tracking-widest uppercase">WELCOME, ASSOCIATION ADMIN</h1>
                <p class="text-xs text-slate-400 max-w-xl">Association monitoring database interface active. Oversee hunter registrations, contract enforcement, S-Rank authorizations, and financial records.</p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
                <button wire:click="loadStats" class="px-5 py-2.5 bg-gradient-to-r from-neon-purple to-pink-500 text-white font-title font-black text-[10px] tracking-widest rounded-xl transition duration-300 shadow-neon-purple/25 hover:opacity-90">
                    REFRESH MONITORS
                </button>
            </div>
        </div>
    </div>

    <!-- Today's Monitor Overview -->
    <div class="space-y-4">
        <h3 class="font-title text-xs font-black text-slate-400 tracking-widest uppercase">DAILY HUD SUMMARY (TODAY)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- New Users -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 flex items-center justify-between shadow-lg">
                <div class="space-y-1">
                    <span class="text-[10px] text-slate-500 font-title font-bold tracking-widest block">NEW HUNTERS REGISTERED</span>
                    <div class="text-white font-title text-2xl font-black">{{ $todayStats['new_users'] }}</div>
                </div>
                <div class="w-12 h-12 bg-neon-blue/10 border border-neon-blue/20 rounded-xl flex items-center justify-center text-neon-blue font-title font-bold">
                    +{{ $stats['total_users'] > 0 ? round(($todayStats['new_users'] / $stats['total_users']) * 100, 1) : 0 }}%
                </div>
            </div>

            <!-- New Payments -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 flex items-center justify-between shadow-lg">
                <div class="space-y-1">
                    <span class="text-[10px] text-slate-500 font-title font-bold tracking-widest block">NEW TRANSACTIONS</span>
                    <div class="text-white font-title text-2xl font-black">{{ $todayStats['new_payments'] }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/10 border border-green-500/20 rounded-xl flex items-center justify-center text-green-400 font-title font-bold">
                    ₹{{ $todayStats['revenue_today'] }}
                </div>
            </div>

            <!-- Contracts Created -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 flex items-center justify-between shadow-lg">
                <div class="space-y-1">
                    <span class="text-[10px] text-slate-500 font-title font-bold tracking-widest block">CONTRACTS AWAKENED</span>
                    <div class="text-white font-title text-2xl font-black">{{ $todayStats['contracts_created'] }}</div>
                </div>
                <div class="w-12 h-12 bg-neon-purple/10 border border-neon-purple/20 rounded-xl flex items-center justify-center text-neon-purple font-title font-bold">
                    ACTIVE
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 flex items-center justify-between shadow-lg">
                <div class="space-y-1">
                    <span class="text-[10px] text-slate-500 font-title font-bold tracking-widest block">TOTAL REVENUE (ALL)</span>
                    <div class="text-green-400 font-title text-2xl font-black">₹{{ number_format($stats['total_revenue']) }}</div>
                </div>
                <div class="w-12 h-12 bg-gold-rpg/10 border border-gold-rpg/20 rounded-xl flex items-center justify-center text-gold-rpg font-title font-bold">
                    ₹
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Grid Monitor -->
    <div class="space-y-4">
        <h3 class="font-title text-xs font-black text-slate-400 tracking-widest uppercase">ASSOCIATION DATABASE METRICS</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Users Card -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(59,130,246,0.01)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div>
                        <h4 class="font-title text-sm font-black text-white tracking-widest uppercase">👥 HUNTERS REGISTRATION</h4>
                        <p class="text-[9px] text-slate-500 font-mono font-bold tracking-wider">DATABASE_USER_STATUS_OVERVIEW</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">TOTAL REGISTERED</span>
                        <div class="text-white text-xl font-bold font-title mt-0.5">{{ $stats['total_users'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">ACTIVE USERS</span>
                        <div class="text-neon-blue text-xl font-bold font-title mt-0.5">{{ $stats['active_users'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">PAID MEMBERS</span>
                        <div class="text-green-400 text-xl font-bold font-title mt-0.5">{{ $stats['paid_users'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">UNPAID TRIALS</span>
                        <div class="text-gray-400 text-xl font-bold font-title mt-0.5">{{ $stats['unpaid_users'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Contracts Card -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(139,92,246,0.01)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div>
                        <h4 class="font-title text-sm font-black text-white tracking-widest uppercase">⚔️ SYSTEM CONTRACTS</h4>
                        <p class="text-[9px] text-slate-500 font-mono font-bold tracking-wider">RECURRING_HABIT_TIMELINE_METRICS</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">TOTAL CREATED</span>
                        <div class="text-white text-xl font-bold font-title mt-0.5">{{ $stats['total_contracts'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">CREATED TODAY</span>
                        <div class="text-neon-purple text-xl font-bold font-title mt-0.5">{{ $stats['contracts_today'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">CREATED MONTH</span>
                        <div class="text-neon-blue text-xl font-bold font-title mt-0.5">{{ $stats['contracts_month'] }}</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">AVG CONTRACTS/USER</span>
                        <div class="text-gray-300 text-xl font-bold font-title mt-0.5">
                            {{ $stats['total_users'] > 0 ? round($stats['total_contracts'] / $stats['total_users'], 1) : 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Card -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(245,158,11,0.01)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div>
                        <h4 class="font-title text-sm font-black text-white tracking-widest uppercase">💎 PREMIUM FEATURES</h4>
                        <p class="text-[9px] text-slate-500 font-mono font-bold tracking-wider">ENTITLEMENT_ADOPTION_REPORTS</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">ELITE SKILLS</span>
                        <div class="text-gold-rpg text-xl font-bold font-title mt-0.5">{{ $stats['elite_skills'] }} Users</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">PERSONAL DOMAINS</span>
                        <div class="text-gold-rpg text-xl font-bold font-title mt-0.5">{{ $stats['personal_domain'] }} Users</div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">SKILLS ADOPTION</span>
                        <div class="text-gray-300 text-xl font-bold font-title mt-0.5">
                            {{ $stats['total_users'] > 0 ? round(($stats['elite_skills'] / $stats['total_users']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    <div>
                        <span class="text-[9px] text-slate-500 font-title font-bold tracking-widest uppercase block">DOMAINS ADOPTION</span>
                        <div class="text-gray-300 text-xl font-bold font-title mt-0.5">
                            {{ $stats['total_users'] > 0 ? round(($stats['personal_domain'] / $stats['total_users']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
