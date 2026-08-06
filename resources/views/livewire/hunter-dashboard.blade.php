<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\SystemContract;
use App\Models\ContractCheckin;
use App\Models\LifeDomain;
use App\Models\EliteSkill;
use App\Models\DailyQuest;
use App\Services\XPEngineService;
use App\Services\AIShadowGuideService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public ?User $user = null;
    public ?LifeDomain $lifeDomain = null;
    public $skills = [];
    public ?SystemContract $activeContract = null;
    public $activeContracts = [];
    public $checkins = [];
    public $dailyQuests = [];
    public string $aiCoachingMessage = '';
    public array $aiGrowthAssessment = [];

    // Form/Modal states
    public bool $showContractModal = false;
    public string $newContractTitle = '';
    public string $newContractDifficulty = 'Easy';
    public int $newContractDuration = 7;

    // Skill Points state
    public ?int $selectedSkillId = null;

    // Life Domain Edit state
    public bool $isEditingDomains = false;
    public array $editMetrics = [];
    public array $newMetricLabels = [
        'physical' => '',
        'mental' => '',
        'finance' => '',
        'relationship' => '',
        'career' => '',
        'spirituality' => '',
    ];

    // Paywall and Trial states
    public bool $contractsTrialExpired = false;
    public bool $domainsTrialExpired = false;
    public bool $skillsTrialExpired = false;
    public int $contractsDaysLeft = 7;
    public int $domainsDaysLeft = 3;
    public int $skillsDaysLeft = 3;

    public function mount()
    {
        $this->user = Auth::user();
        $this->initializeHunterData();
        $this->loadDashboardData();
    }

    protected function getDefaultMetricsStructure(): array
    {
        return [
            'physical' => [
                ['name' => 'Sleep Quality', 'score' => 20, 'completed' => true],
                ['name' => 'Exercise Consistency', 'score' => 20, 'completed' => true],
                ['name' => 'Body Weight / Composition', 'score' => 20, 'completed' => true],
                ['name' => 'Nutrition Quality', 'score' => 20, 'completed' => false],
                ['name' => 'Energy Levels', 'score' => 20, 'completed' => false],
            ],
            'mental' => [
                ['name' => 'Stress Management', 'score' => 20, 'completed' => true],
                ['name' => 'Emotional Control', 'score' => 20, 'completed' => true],
                ['name' => 'Focus & Concentration', 'score' => 20, 'completed' => true],
                ['name' => 'Confidence', 'score' => 20, 'completed' => false],
                ['name' => 'Happiness Level', 'score' => 20, 'completed' => false],
            ],
            'finance' => [
                ['name' => 'Income Growth', 'score' => 20, 'completed' => true],
                ['name' => 'Savings Rate', 'score' => 20, 'completed' => true],
                ['name' => 'Emergency Fund', 'score' => 20, 'completed' => false],
                ['name' => 'Investments', 'score' => 20, 'completed' => false],
                ['name' => 'Debt Reduction', 'score' => 20, 'completed' => false],
            ],
            'relationship' => [
                ['name' => 'Family Connection', 'score' => 20, 'completed' => true],
                ['name' => 'Romantic Relationship', 'score' => 20, 'completed' => true],
                ['name' => 'Friendships', 'score' => 20, 'completed' => true],
                ['name' => 'Communication Skills', 'score' => 20, 'completed' => true],
                ['name' => 'Social Support', 'score' => 20, 'completed' => false],
            ],
            'career' => [
                ['name' => 'Job Performance', 'score' => 20, 'completed' => true],
                ['name' => 'Skill Development', 'score' => 20, 'completed' => true],
                ['name' => 'Productivity', 'score' => 20, 'completed' => true],
                ['name' => 'Leadership', 'score' => 20, 'completed' => false],
                ['name' => 'Work Satisfaction', 'score' => 20, 'completed' => false],
            ],
            'spirituality' => [
                ['name' => 'Purpose in Life', 'score' => 20, 'completed' => true],
                ['name' => 'Meditation', 'score' => 20, 'completed' => true],
                ['name' => 'Gratitude', 'score' => 20, 'completed' => true],
                ['name' => 'Mindfulness', 'score' => 20, 'completed' => true],
                ['name' => 'Inner Peace', 'score' => 20, 'completed' => false],
            ],
        ];
    }

    protected function initializeHunterData()
    {
        // Initialize billing trials
        $shouldSave = false;
        if (!$this->user->contracts_trial_started_at) {
            $this->user->contracts_trial_started_at = Carbon::now();
            $shouldSave = true;
        }
        if (!$this->user->domains_trial_started_at) {
            $this->user->domains_trial_started_at = Carbon::now();
            $shouldSave = true;
        }
        if (!$this->user->skills_trial_started_at) {
            $this->user->skills_trial_started_at = Carbon::now();
            $shouldSave = true;
        }
        if ($shouldSave) {
            $this->user->save();
        }

        if (!$this->user->lifeDomain) {
            $lifeDomain = LifeDomain::create([
                'user_id' => $this->user->id,
                'health_physical_score' => 60,
                'health_mental_score' => 65,
                'finance_score' => 48,
                'relationship_score' => 80,
                'career_score' => 61,
                'spirituality_score' => 70,
                'overall_life_score' => 64,
                'metrics_data' => $this->getDefaultMetricsStructure(),
            ]);
            $lifeDomain->recalculateOverallScore();
        }

        $ld = $this->user->lifeDomain;
        if ($ld && empty($ld->metrics_data)) {
            $ld->metrics_data = $this->getDefaultMetricsStructure();
            $ld->recalculateOverallScore();
        }

        $coreSkills = [
            'Emotional Management (Shadow)' => ['eq' => 10, 'stress' => 10],
            'Innovative Thinking (Elite)' => ['iq' => 10, 'creativity' => 10],
            'Super Memory (Rare)' => ['retention' => 10, 'recall' => 10],
            'Heightened Sensory (Awakened)' => ['awareness' => 10, 'focus' => 10],
            'Multi-Tasking (Epic)' => ['parallel' => 10, 'speed' => 10],
            'General Mind Stimulation (Legendary)' => ['synaptic' => 10, 'stamina' => 10]
        ];

        foreach ($coreSkills as $skillName => $subtracks) {
            $exists = EliteSkill::where('user_id', $this->user->id)
                ->where('skill_name', $skillName)
                ->exists();

            if (!$exists) {
                EliteSkill::create([
                    'user_id' => $this->user->id,
                    'skill_name' => $skillName,
                    'level' => 1,
                    'xp' => 0,
                    'sub_tracks_scores' => $subtracks
                ]);
            }
        }

        $today = Carbon::today();
        $questsCount = DailyQuest::where('user_id', $this->user->id)
            ->whereDate('quest_date', $today)
            ->count();

        if ($questsCount === 0) {
            $defaultQuests = [
                'Physical Awakening: 100 push-ups, 100 squats, 100 sit-ups',
                'Shadow Coding: 2 hours of project design or software development',
                'Mind Stimulation: 30 minutes reading technical docs or memory recall'
            ];

            foreach ($defaultQuests as $questText) {
                DailyQuest::create([
                    'user_id' => $this->user->id,
                    'quest_date' => $today,
                    'description' => $questText,
                    'is_completed' => false
                ]);
            }
        }
    }

    protected function loadDashboardData()
    {
        $this->user->refresh();
        $this->lifeDomain = $this->user->lifeDomain;
        $this->skills = $this->user->eliteSkills()->get();

        // Dynamically scan for missed check-ins on all active contracts
        $activeContractsRaw = $this->user->systemContracts()
            ->where('status', 'active')
            ->get();

        $hasFailures = false;
        foreach ($activeContractsRaw as $contract) {
            $start = Carbon::parse($contract->start_date)->startOfDay();
            $today = Carbon::today();
            $currentDayNum = $start->diffInDays($today) + 1;

            // Check if any day BEFORE today is NOT checked
            $missedCheckin = $contract->checkins()
                ->where('day_number', '<', $currentDayNum)
                ->where('is_checked', false)
                ->first();

            if ($missedCheckin) {
                $contract->status = 'failed';
                $contract->failed_at = Carbon::now();
                $contract->save();

                $this->user->current_streak = 0;
                $this->user->save();

                event(new \App\Events\ContractBroken($contract));
                session()->flash('error', "MISSION FAILED: System Contract '{$contract->title}' has failed due to missed check-in on Day {$missedCheckin->day_number}! Streaks reset to 0.");
                $hasFailures = true;
            }
        }

        if ($hasFailures) {
            $this->user->refresh();
        }

        $this->activeContracts = $this->user->systemContracts()
            ->where('status', 'active')
            ->get();
        $this->activeContract = $this->activeContracts->first();

        if ($this->activeContract) {
            $this->checkins = $this->activeContract->checkins()
                ->orderBy('day_number', 'asc')
                ->get();
        } else {
            $this->checkins = [];
        }

        $this->dailyQuests = DailyQuest::where('user_id', $this->user->id)
            ->whereDate('quest_date', Carbon::today())
            ->get();

        $aiService = new AIShadowGuideService();
        $this->aiCoachingMessage = $aiService->generateDailyCoachingMessage($this->user);
        $this->aiGrowthAssessment = $aiService->assessWeeklyGrowthPatterns($this->user);

        if ($this->lifeDomain) {
            $this->editMetrics = $this->lifeDomain->metrics_data ?? $this->getDefaultMetricsStructure();
        }

        // Calculate paywall conditions
        $now = Carbon::now()->startOfDay();
        
        $contractsStart = $this->user->contracts_trial_started_at ? Carbon::parse($this->user->contracts_trial_started_at)->startOfDay() : $now;
        $contractsElapsed = (int) abs($now->diffInDays($contractsStart));
        $this->contractsDaysLeft = max(0, 7 - $contractsElapsed);
        $this->contractsTrialExpired = ($contractsElapsed >= 7 && !$this->user->is_contracts_paid);

        $domainsStart = $this->user->domains_trial_started_at ? Carbon::parse($this->user->domains_trial_started_at)->startOfDay() : $now;
        $domainsElapsed = (int) abs($now->diffInDays($domainsStart));
        $this->domainsDaysLeft = max(0, 3 - $domainsElapsed);
        $this->domainsTrialExpired = ($domainsElapsed >= 3 && !$this->user->is_domains_paid);

        $skillsStart = $this->user->skills_trial_started_at ? Carbon::parse($this->user->skills_trial_started_at)->startOfDay() : $now;
        $skillsElapsed = (int) abs($now->diffInDays($skillsStart));
        $this->skillsDaysLeft = max(0, 3 - $skillsElapsed);
        $this->skillsTrialExpired = ($skillsElapsed >= 3 && !$this->user->is_skills_paid);
    }

    public function checkIn($contractId = null)
    {
        if ($this->contractsTrialExpired) {
            session()->flash('error', 'System Contracts are locked. Upgrade to unlock.');
            return;
        }

        $contract = null;
        if ($contractId) {
            $contract = $this->user->systemContracts()->where('status', 'active')->find($contractId);
        } else {
            $contract = $this->user->systemContracts()->where('status', 'active')->first();
        }

        if (!$contract) {
            session()->flash('error', 'No active contract to check in.');
            return;
        }

        $start = Carbon::parse($contract->start_date)->startOfDay();
        $today = Carbon::today();
        $dayNumber = $start->diffInDays($today) + 1;

        if ($dayNumber > $contract->duration_days) {
            session()->flash('error', 'Contract duration has passed.');
            return;
        }

        // Enforce consecutive day check-in validation
        $missedCheckin = $contract->checkins()
            ->where('day_number', '<', $dayNumber)
            ->where('is_checked', false)
            ->first();

        if ($missedCheckin) {
            $contract->status = 'failed';
            $contract->failed_at = Carbon::now();
            $contract->save();

            $this->user->current_streak = 0;
            $this->user->save();

            event(new \App\Events\ContractBroken($contract));
            session()->flash('error', "MISSION FAILED: System Contract '{$contract->title}' has failed due to missed check-in on Day {$missedCheckin->day_number}! Streaks reset to 0.");
            $this->loadDashboardData();
            return;
        }

        $checkin = $contract->checkins()
            ->where('day_number', $dayNumber)
            ->first();

        if (!$checkin) {
            session()->flash('error', 'Failed to retrieve check-in node.');
            return;
        }

        if ($checkin->is_checked) {
            session()->flash('warning', 'Already checked in for today!');
            return;
        }

        $checkin->is_checked = true;
        $checkin->completed_at = Carbon::now();
        $checkin->save();

        $this->user->current_streak++;
        if ($this->user->current_streak > $this->user->highest_streak) {
            $this->user->highest_streak = $this->user->current_streak;
        }
        $this->user->save();

        XPEngineService::award($this->user, 'daily_login');

        $pendingCount = $contract->checkins()
            ->where('is_checked', false)
            ->count();

        if ($pendingCount === 0) {
            $contract->status = 'completed';
            $contract->save();

            $this->user->gold += $contract->gold_reward;
            $this->user->addXp($contract->xp_reward);

            XPEngineService::award($this->user, 'contract_completion');

            session()->flash('success', "CONTRACT COMPLETED! You gained {$contract->xp_reward} XP and {$contract->gold_reward} Gold!");
        } else {
            session()->flash('success', "Check-in successful for Day {$dayNumber}!");
        }

        $this->loadDashboardData();
    }

    public function acceptContract()
    {
        if ($this->contractsTrialExpired) {
            session()->flash('error', 'System Contracts are locked. Upgrade to unlock.');
            return;
        }

        $activeCount = $this->user->systemContracts()->where('status', 'active')->count();
        if ($activeCount >= 5) {
            session()->flash('error', 'You have reached the maximum limit of 5 active system contracts.');
            return;
        }

        $this->validate([
            'newContractTitle' => 'required|string|max:100',
            'newContractDifficulty' => 'required|in:Easy,Medium,Hard,Elite',
            'newContractDuration' => 'required|in:7,21,51,71',
        ]);

        $diffMultiplier = [
            'Easy' => 1,
            'Medium' => 2,
            'Hard' => 3,
            'Elite' => 5
        ][$this->newContractDifficulty];

        $durationXp = $this->newContractDuration * 20;
        $xpReward = $durationXp * $diffMultiplier;
        $goldReward = $xpReward * 2;

        SystemContract::create([
            'user_id' => $this->user->id,
            'title' => $this->newContractTitle,
            'description' => "Stay consistent for {$this->newContractDuration} days to fulfill this contract.",
            'duration_days' => $this->newContractDuration,
            'difficulty' => $this->newContractDifficulty,
            'xp_reward' => $xpReward,
            'gold_reward' => $goldReward,
            'status' => 'active',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays($this->newContractDuration - 1),
        ]);

        $this->showContractModal = false;
        $this->newContractTitle = '';

        session()->flash('success', 'New contract accepted! System checklist generated.');
        $this->loadDashboardData();
    }

    public function toggleQuest($questId)
    {
        $quest = DailyQuest::where('user_id', $this->user->id)
            ->findOrFail($questId);

        if (!$quest->is_completed) {
            $quest->is_completed = true;
            $quest->completed_at = Carbon::now();
            $quest->save();

            XPEngineService::award($this->user, 'daily_quest');
            session()->flash('success', 'Quest completed! +25 XP');
        }

        $this->loadDashboardData();
    }

    public function spendSkillPoint($skillId)
    {
        if ($this->skillsTrialExpired) {
            session()->flash('error', 'Elite System Skills are locked. Upgrade to unlock.');
            return;
        }

        if ($this->user->skill_points <= 0) {
            session()->flash('error', 'No skill points available.');
            return;
        }

        $skill = EliteSkill::where('user_id', $this->user->id)->findOrFail($skillId);
        
        $skill->level++;
        $this->user->skill_points--;
        $this->user->save();

        $scores = $skill->sub_tracks_scores;
        if (is_array($scores)) {
            foreach ($scores as $key => $val) {
                $scores[$key] = $val + 2;
            }
            $skill->sub_tracks_scores = $scores;
        }

        $skill->save();

        XPEngineService::award($this->user, 'skill_challenge');
        session()->flash('success', "Upgraded {$skill->skill_name} to Level {$skill->level}!");
        $this->loadDashboardData();
    }

    public function toggleEditDomains()
    {
        $this->isEditingDomains = !$this->isEditingDomains;
    }

    public function addCustomMetric(string $domainKey)
    {
        $label = trim($this->newMetricLabels[$domainKey] ?? '');
        if ($label === '') {
            session()->flash('error', 'Metric name cannot be empty.');
            return;
        }

        if (!isset($this->editMetrics[$domainKey])) {
            $this->editMetrics[$domainKey] = [];
        }

        // Check if metric already exists
        foreach ($this->editMetrics[$domainKey] as $m) {
            if (strtolower($m['name']) === strtolower($label)) {
                session()->flash('error', 'Metric already exists.');
                return;
            }
        }

        $this->editMetrics[$domainKey][] = [
            'name' => $label,
            'score' => 20,
            'completed' => false
        ];

        $this->newMetricLabels[$domainKey] = '';
        session()->flash('success', 'Custom metric added!');
    }

    public function removeMetric(string $domainKey, int $index)
    {
        if (isset($this->editMetrics[$domainKey][$index])) {
            unset($this->editMetrics[$domainKey][$index]);
            // Reindex array
            $this->editMetrics[$domainKey] = array_values($this->editMetrics[$domainKey]);
            session()->flash('success', 'Metric removed.');
        }
    }

    public function saveDomains()
    {
        if ($this->domainsTrialExpired) {
            session()->flash('error', 'Life Domain Scorecard is locked. Upgrade to unlock.');
            return;
        }

        // Validate metrics: each score must be between 0 and 20, completed must be boolean
        $rules = [];
        foreach ($this->editMetrics as $domainKey => $metrics) {
            foreach ($metrics as $index => $metric) {
                $rules["editMetrics.{$domainKey}.{$index}.score"] = 'required|integer|between:0,20';
                $rules["editMetrics.{$domainKey}.{$index}.name"] = 'required|string|max:100';
                $rules["editMetrics.{$domainKey}.{$index}.completed"] = 'required|boolean';
            }
        }
        $this->validate($rules);

        $this->lifeDomain->metrics_data = $this->editMetrics;
        $this->lifeDomain->recalculateOverallScore();

        if ($this->lifeDomain->overall_life_score >= 80) {
            XPEngineService::award($this->user, 'life_domain_milestone');
        }

        $this->isEditingDomains = false;
        session()->flash('success', 'Life Domains re-evaluated!');
        $this->loadDashboardData();
    }

    public function payForContracts()
    {
        $this->user->is_contracts_paid = true;
        $this->user->gold += 500; // gold payout
        $this->user->save();

        XPEngineService::award($this->user, 'achievement'); // +100 XP
        session()->flash('success', 'TRANSACTION SUCCESSFUL: Accepted 99 Rs via UPI/Razorpay. Contracts unlocked indefinitely! +100 XP gained!');
        $this->loadDashboardData();
    }

    public function payForDomains()
    {
        $this->user->is_domains_paid = true;
        $this->user->gold += 1000;
        $this->user->save();

        XPEngineService::award($this->user, 'achievement'); // +100 XP
        session()->flash('success', 'TRANSACTION SUCCESSFUL: Accepted 199 Rs via UPI/Razorpay. Domains Scorecard unlocked indefinitely! +100 XP gained!');
        $this->loadDashboardData();
    }

    public function payForSkills()
    {
        $this->user->is_skills_paid = true;
        $this->user->gold += 1500;
        $this->user->save();

        XPEngineService::award($this->user, 'achievement'); // +100 XP
        session()->flash('success', 'TRANSACTION SUCCESSFUL: Accepted 299 Rs via UPI/Razorpay. S-Rank Skills unlocked indefinitely! +100 XP gained!');
        $this->loadDashboardData();
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">
    
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl flex items-center gap-3">
            <span>✓</span>
            <div class="text-sm font-semibold">{{ session('success') }}</div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl flex items-center gap-3">
            <span>⚠</span>
            <div class="text-sm font-semibold">{{ session('error') }}</div>
        </div>
    @endif

    <!-- STATUS HEADER PANEL (Sci-Fi HUD styling) -->
    <div class="bg-obsidian-card border border-white/10 rounded-2xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
        <!-- Corner Marks -->
        <div class="absolute top-2 left-2 w-3 h-3 border-t-2 border-l-2 border-neon-blue/40 pointer-events-none"></div>
        <div class="absolute top-2 right-2 w-3 h-3 border-t-2 border-r-2 border-neon-blue/40 pointer-events-none"></div>
        <div class="absolute bottom-2 left-2 w-3 h-3 border-b-2 border-l-2 border-neon-blue/40 pointer-events-none"></div>
        <div class="absolute bottom-2 right-2 w-3 h-3 border-b-2 border-r-2 border-neon-blue/40 pointer-events-none"></div>

        <div class="absolute top-[-10%] right-[-10%] w-[300px] h-[300px] bg-neon-blue/5 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
            <!-- Rank Badge (Circular gauge) & Level Details -->
            <div class="flex items-center gap-6">
                <!-- Circular Level gauge -->
                <div class="relative w-20 h-20 shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="rgba(255,255,255,0.05)" stroke-width="4" fill="transparent"/>
                        <circle cx="50" cy="50" r="42" stroke="url(#neonGradient)" stroke-width="6" fill="transparent"
                                stroke-dasharray="264"
                                stroke-dashoffset="{{ 264 - (264 * min(100, ($user->xp / ($user->level * 100)) )) / 100 }}"
                                class="transition-all duration-700"/>
                        <defs>
                            <linearGradient id="neonGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#45f3ff" />
                                <stop offset="50%" stop-color="#8a2be2" />
                                <stop offset="100%" stop-color="#ffd700" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- Center Rank Text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-title font-black tracking-widest text-slate-400">RANK</span>
                        <span class="font-title font-black text-lg text-white leading-none tracking-tighter">{{ $user->rank }}</span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <h2 class="font-title text-2xl font-black text-white tracking-wide uppercase bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">
                        {{ $user->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold">
                        <span class="text-neon-purple font-title font-black tracking-wider">LEVEL {{ $user->level }}</span>
                        <span class="text-white/20">•</span>
                        <span class="text-gold-rpg font-title font-bold tracking-wider">{{ $user->gold }} GOLD</span>
                        <span class="text-white/20">•</span>
                        <span class="text-neon-blue font-title font-bold tracking-wider">{{ $user->skill_points }} SKILL POINTS</span>
                    </div>
                </div>
            </div>

            <!-- Streaks Diagnostic indicators -->
            <div class="flex gap-6 border-l border-white/5 pl-6 md:border-l-0 md:pl-0">
                <div>
                    <div class="text-slate-400 text-xs font-title tracking-widest font-bold">CURRENT STREAK</div>
                    <div class="text-neon-blue font-title font-black text-2xl">{{ $user->current_streak }} DAYS</div>
                </div>
                <div>
                    <div class="text-slate-400 text-xs font-title tracking-widest font-bold">HIGHEST RECORD</div>
                    <div class="text-neon-purple font-title font-black text-2xl">{{ $user->highest_streak }} DAYS</div>
                </div>
            </div>
        </div>

        <!-- Level XP Bar -->
        <div class="mt-8 space-y-2">
            <div class="flex justify-between text-xs font-title font-bold tracking-widest text-slate-300">
                <span>XP PROGRESS ({{ $user->xp }} / {{ $user->level * 100 }} XP)</span>
                <span>{{ round(($user->xp / ($user->level * 100)) * 100) }}% TO NEXT LEVEL</span>
            </div>
            <div class="w-full bg-black/60 h-2.5 rounded-full overflow-hidden border border-white/5">
                <div class="bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg h-full rounded-full transition-all duration-500" style="width: {{ min(100, ($user->xp / ($user->level * 100)) * 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN BODY -->
    <div class="grid lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT COLUMN: active contracts, domain visualizer, skills -->
        <div class="lg:col-span-8 space-y-8">
                 <!-- ACTIVE CONTRACT CARD -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-xl space-y-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(69,243,255,0.02)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>

                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-neon-blue/10 border border-neon-blue/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-neon-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-title text-lg font-black text-white tracking-wide">ACTIVE SYSTEM CONTRACTS</h3>
                            <p class="text-[10px] text-gray-500 font-bold tracking-wider">RECURRING HABIT STRATEGY TIMELINE // {{ count($activeContracts) }} ACTIVE</p>
                        </div>
                    </div>

                    @if (count($activeContracts) < 5 && !$contractsTrialExpired)
                        <button 
                            wire:click="$set('showContractModal', true)"
                            class="px-4 py-2.5 bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-title font-black text-xs tracking-wider rounded-lg shadow-neon-blue hover:opacity-90 transition"
                        >
                            AWAKEN CONTRACT ({{ count($activeContracts) }}/5)
                        </button>
                    @endif
                </div>

                @if ($contractsTrialExpired)
                    <div class="text-center py-10 bg-black/40 border border-red-500/20 rounded-xl space-y-4 p-6">
                        <div class="w-12 h-12 bg-red-500/10 border border-red-500/20 rounded-full flex items-center justify-center mx-auto text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h4 class="font-title font-black text-red-400 mt-2 tracking-wider">SYSTEM CONTRACT LOCKED</h4>
                        <p class="text-xs text-gray-400 max-w-md mx-auto leading-relaxed">
                            Your 7-day contract trial has expired. To continue accepting daily system contracts and ascending Hunter Ranks, pay 99 Rs via Razorpay / UPI.
                        </p>
                        <div class="pt-2">
                            <button 
                                wire:click="payForContracts"
                                class="px-8 py-3.5 bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue hover:scale-102 hover:opacity-95 transition duration-300"
                            >
                                UNLOCK CONTRACTS INDEFINITELY // 99 Rs
                            </button>
                        </div>
                    </div>
                @else
                    @if (count($activeContracts) > 0)
                        <div class="space-y-8">
                            @foreach ($activeContracts as $idx => $contract)
                                <div class="bg-black/25 border border-white/5 rounded-xl p-5 space-y-4">
                                    <!-- Contract stats info -->
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-3 border-b border-white/5">
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-title font-bold tracking-widest block uppercase">CONTRACT TITLE</span>
                                            <div class="text-white font-bold text-sm mt-0.5">{{ $contract->title }}</div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-title font-bold tracking-widest block uppercase">DIFFICULTY / DURATION</span>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-title font-black {{ $contract->difficulty === 'Elite' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-neon-purple/10 text-neon-purple border border-neon-purple/20' }}">
                                                        {{ strtoupper($contract->difficulty) }}
                                                    </span>
                                                    <span class="text-gray-300 text-xs font-semibold">{{ $contract->duration_days }} Days</span>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-title font-bold tracking-widest block uppercase">REWARDS</span>
                                                <div class="text-gold-rpg font-title font-bold text-xs mt-0.5">
                                                    +{{ $contract->xp_reward }} XP // +{{ $contract->gold_reward }} G
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Check-in nodes -->
                                    <div>
                                        <span class="text-[10px] text-slate-300 font-title font-bold tracking-widest block mb-2">CHECK-IN TIMELINE MATRIX</span>
                                        <div class="grid grid-cols-7 sm:grid-cols-10 md:grid-cols-14 gap-1.5">
                                            @php
                                                $start = Carbon::parse($contract->start_date)->startOfDay();
                                                $today = Carbon::today();
                                                $dayNumber = $start->diffInDays($today) + 1;
                                                $checkins = $contract->checkins()->orderBy('day_number', 'asc')->get();
                                            @endphp
                                            @foreach ($checkins as $c)
                                                @php
                                                    $isCurrentDay = ($c->day_number === $dayNumber);
                                                    $nodeDate = Carbon::parse($contract->start_date)->addDays($c->day_number - 1);
                                                @endphp
                                                <div 
                                                    class="aspect-square flex flex-col items-center justify-center rounded border text-[10px] font-title font-bold transition-all duration-300 p-1
                                                    {{ $c->is_checked ? 'bg-neon-blue/15 border-neon-blue text-neon-blue shadow-neon-blue/15' : '' }}
                                                    {{ !$c->is_checked && $isCurrentDay ? 'bg-obsidian-light border-neon-purple text-neon-purple animate-pulse shadow-neon-purple' : '' }}
                                                    {{ !$c->is_checked && !$isCurrentDay ? 'bg-black/40 border-white/5 text-gray-600' : '' }}
                                                    "
                                                    title="Day {{ $c->day_number }} ({{ $nodeDate->toDateString() }}) {{ $c->is_checked ? '(Completed)' : ($isCurrentDay ? '(Active Today)' : '(Locked)') }}"
                                                >
                                                    <span class="block text-[10px]">{{ $c->day_number }}</span>
                                                    <span class="block text-[8px] opacity-75 mt-0.5">{{ $nodeDate->format('d M') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Execution check-in button -->
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-[10px] text-slate-500 font-title font-bold tracking-wide">
                                            [ CONTRACT {{ $idx + 1 }} OF {{ count($activeContracts) }} ]
                                        </span>
                                        @php
                                            $todayCheckin = $contract->checkins()->where('day_number', $dayNumber)->first();
                                            $alreadyChecked = $todayCheckin ? $todayCheckin->is_checked : false;
                                        @endphp
                                        
                                        @if ($alreadyChecked)
                                            <button disabled class="px-4 py-2 rounded border border-white/5 bg-obsidian-light text-slate-500 font-title font-black text-[10px] tracking-widest uppercase">
                                                COMPLETED TODAY
                                            </button>
                                        @else
                                            <button 
                                                wire:click="checkIn({{ $contract->id }})"
                                                class="px-5 py-2.5 rounded bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-title font-black text-[10px] tracking-widest shadow-neon-blue hover:scale-102 transition duration-300 animate-pulse"
                                            >
                                                EXECUTE DAY {{ $dayNumber }} CHECK-IN
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-black/40 border border-dashed border-white/10 rounded-xl">
                            <span class="text-4xl">💀</span>
                            <h4 class="font-title font-black text-gray-500 mt-4 tracking-wider">NO ACTIVE SYSTEM CONTRACTS</h4>
                            <p class="text-xs text-gray-600 mt-2 max-w-sm mx-auto">Awaken a contract (up to 5 active simultaneously) to structure your habits. Consistency unlocks your progression. Failure breaks your record streak.</p>
                        </div>
                    @endif
                @endif
            </div>

            <!-- LIFE DOMAIN SCORECARD -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-neon-purple/10 border border-neon-purple/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-neon-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-title text-lg font-black text-white tracking-wide">LIFE DOMAIN SCORECARD</h3>
                            <p class="text-[10px] text-gray-500 font-bold tracking-wider">AVERAGE WEIGHTED PROFILE METRICS</p>
                        </div>
                    </div>

                    @if (!$domainsTrialExpired)
                        <button 
                            wire:click="toggleEditDomains"
                            class="px-4 py-2 border border-white/10 text-gray-300 font-title font-bold text-xs tracking-wider rounded-lg hover:border-white/20 transition"
                        >
                            {{ $isEditingDomains ? 'CANCEL' : 'RE-EVALUATE' }}
                        </button>
                    @endif
                </div>

                @if ($domainsTrialExpired)
                    <div class="text-center py-10 bg-black/40 border border-red-500/20 rounded-xl space-y-4 p-6">
                        <div class="w-12 h-12 bg-red-500/10 border border-red-500/20 rounded-full flex items-center justify-center mx-auto text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h4 class="font-title font-black text-red-400 mt-2 tracking-wider">LIFE DOMAIN SCORECARD LOCKED</h4>
                        <p class="text-xs text-gray-400 max-w-md mx-auto leading-relaxed">
                            Your 3-day trial has expired. To continue tracking, re-evaluating, and monitoring your Life Domain Scorecard profile metrics, pay 199 Rs via Razorpay / UPI.
                        </p>
                        <div class="pt-2">
                            <button 
                                wire:click="payForDomains"
                                class="px-8 py-3.5 bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue hover:scale-102 hover:opacity-95 transition duration-300"
                            >
                                UNLOCK SCORECARD INDEFINITELY // 199 Rs
                            </button>
                        </div>
                    </div>
                @else
                    @if ($isEditingDomains)
                        <form wire:submit.prevent="saveDomains" class="space-y-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @php
                                    $domainNames = [
                                        'physical' => ['Body Stat (Physical)', 'text-red-400', 'border-red-500/20'],
                                        'mental' => ['Mind Stat (Mental)', 'text-green-400', 'border-green-500/20'],
                                        'finance' => ['Wealth Stat (Finance)', 'text-gold-rpg', 'border-gold-rpg/20'],
                                        'relationship' => ['Social Stat (Relationships)', 'text-pink-400', 'border-pink-500/20'],
                                        'career' => ['Career Stat (Career)', 'text-neon-blue', 'border-neon-blue/20'],
                                        'spirituality' => ['Soul Stat (Spirituality)', 'text-indigo-400', 'border-indigo-500/20'],
                                    ];
                                @endphp

                                @foreach ($domainNames as $domainKey => $info)
                                    <div class="bg-black/30 border border-white/5 rounded-xl p-5 space-y-4 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-center border-b border-white/5 pb-2 mb-3">
                                                <span class="font-title font-black text-xs tracking-wider {{ $info[1] }}">{{ strtoupper($info[0]) }}</span>
                                                @php
                                                    $sum = 0;
                                                    if (isset($editMetrics[$domainKey])) {
                                                        foreach ($editMetrics[$domainKey] as $m) {
                                                            $isCompleted = !empty($m['completed']);
                                                            if ($isCompleted) {
                                                                $sum += $m['score'] ?? 20;
                                                            }
                                                        }
                                                    }
                                                    $sum = min(100, $sum);
                                                @endphp
                                                <span class="text-xs font-title font-bold text-white">{{ $sum }} / 100</span>
                                            </div>

                                            <div class="space-y-3">
                                                @if (empty($editMetrics[$domainKey]))
                                                    <p class="text-[10px] text-gray-500 italic">No habits tracking in this domain.</p>
                                                @else
                                                    @foreach ($editMetrics[$domainKey] as $index => $metric)
                                                        <div class="bg-white/5 px-3 py-2.5 rounded-lg flex items-center justify-between relative group/metric hover:bg-white/10 transition">
                                                            <div class="flex items-center gap-3 max-w-[80%]">
                                                                <input 
                                                                    type="checkbox" 
                                                                    wire:model.live="editMetrics.{{ $domainKey }}.{{ $index }}.completed" 
                                                                    id="metric_{{ $domainKey }}_{{ $index }}"
                                                                    class="w-4.5 h-4.5 bg-black/40 border border-white/30 text-neon-blue rounded focus:ring-0 focus:ring-offset-0 cursor-pointer transition hover:border-neon-blue/60"
                                                                >
                                                                <label 
                                                                    for="metric_{{ $domainKey }}_{{ $index }}"
                                                                    class="text-xs font-semibold cursor-pointer truncate transition {{ ($metric['completed'] ?? false) ? 'text-white' : 'text-slate-500 line-through' }}"
                                                                >
                                                                    {{ $metric['name'] }}
                                                                </label>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-[10px] font-title font-bold text-slate-500">
                                                                    {{ $metric['score'] ?? 20 }} PTS
                                                                </span>
                                                                <button 
                                                                    type="button"
                                                                    wire:click="removeMetric('{{ $domainKey }}', {{ $index }})"
                                                                    class="text-gray-500 hover:text-red-400 text-xs transition duration-200 ml-1"
                                                                    title="Remove Metric"
                                                                >
                                                                    ✕
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Add new habit/metric form -->
                                        <div class="pt-2 border-t border-white/5 flex gap-1 items-center">
                                            <input 
                                                type="text" 
                                                placeholder="Add custom habit..." 
                                                wire:model="newMetricLabels.{{ $domainKey }}"
                                                class="flex-1 bg-black/50 border border-white/10 text-white rounded px-2 py-1 text-[11px] focus:outline-none focus:border-neon-blue/40"
                                            >
                                            <button 
                                                type="button" 
                                                wire:click="addCustomMetric('{{ $domainKey }}')"
                                                class="bg-neon-blue/20 hover:bg-neon-blue hover:text-obsidian-dark border border-neon-blue/40 text-neon-blue px-2.5 py-1 text-[11px] font-title font-black rounded transition"
                                            >
                                                + ADD
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue">
                                    SAVE EVALUATIONS
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Diagnostic list -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $domains = [
                                    'physical' => ['Physical Health (Body)', $lifeDomain->health_physical_score, 'text-red-400'],
                                    'mental' => ['Mental Health (Mind)', $lifeDomain->health_mental_score, 'text-green-400'],
                                    'finance' => ['Finance (Wealth)', $lifeDomain->finance_score, 'text-gold-rpg'],
                                    'relationship' => ['Relationships (Social)', $lifeDomain->relationship_score, 'text-pink-400'],
                                    'career' => ['Career & Skills (Career)', $lifeDomain->career_score, 'text-neon-blue'],
                                    'spirituality' => ['Spirituality (Soul)', $lifeDomain->spirituality_score, 'text-indigo-400'],
                                ];
                            @endphp

                            @foreach ($domains as $domainKey => $info)
                                <div class="bg-black/40 border border-white/5 rounded-xl p-4 space-y-3 relative overflow-hidden group hover:border-white/10 transition duration-300">
                                    <div class="text-xs text-slate-400 font-title font-bold tracking-widest">{{ strtoupper($info[0]) }}</div>
                                    <div class="flex justify-between items-baseline">
                                        <span class="font-title font-black text-2xl {{ $info[2] }}">{{ $info[1] }}</span>
                                        <span class="text-xs text-slate-500">/ 100</span>
                                    </div>
                                    <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full bg-current {{ $info[2] }} transition-all duration-500" style="width: {{ $info[1] }}%"></div>
                                    </div>

                                    <!-- Compact list of active habits/metrics -->
                                    <div class="space-y-1.5 pt-2 border-t border-white/5 text-[11px]">
                                        @php
                                            $metrics = $lifeDomain->metrics_data[$domainKey] ?? [];
                                        @endphp
                                        @if (empty($metrics))
                                            <div class="text-slate-500 italic text-[10px]">No tracked habits. Click Re-Evaluate.</div>
                                        @else
                                            @foreach (array_slice($metrics, 0, 5) as $m)
                                                @php
                                                    $isCompleted = !empty($m['completed']);
                                                @endphp
                                                <div class="flex justify-between items-center text-[11px] font-semibold transition">
                                                    <div class="flex items-center gap-2 truncate max-w-[80%]">
                                                        @if ($isCompleted)
                                                            <span class="text-neon-blue font-bold">✓</span>
                                                            <span class="text-gray-300 truncate">{{ $m['name'] }}</span>
                                                        @else
                                                            <span class="text-slate-600 font-bold">○</span>
                                                            <span class="text-slate-500 truncate line-through">{{ $m['name'] }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="font-title font-bold text-[10px] {{ $isCompleted ? 'text-neon-blue' : 'text-slate-600' }}">
                                                        {{ $isCompleted ? '+' . ($m['score'] ?? 20) : '0' }} PTS
                                                    </span>
                                                </div>
                                            @endforeach
                                            @if (count($metrics) > 5)
                                                <div class="text-[10px] text-slate-500 font-semibold italic text-right">+{{ count($metrics) - 5 }} more habits</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Rank panel details -->
                        <div class="bg-black border border-white/5 rounded-xl p-6 flex flex-col sm:flex-row justify-between items-center gap-4 relative overflow-hidden">
                            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-neon-purple/5 to-transparent pointer-events-none"></div>
                            <div>
                                <div class="text-xs text-slate-400 font-title font-bold tracking-widest">CUMULATIVE AVERAGE PROFILE SCORE</div>
                                <div class="font-title font-black text-3xl text-white">{{ $lifeDomain->overall_life_score }} <span class="text-xs text-slate-500">/ 100</span></div>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-slate-400 text-xs font-title font-bold tracking-widest">CURRENT LIFE RANK:</span>
                                <span class="px-4 py-2.5 rounded-xl bg-gold-rpg/10 border border-gold-rpg text-gold-rpg font-title font-black text-xs tracking-widest shadow-neon-gold">
                                    {{ strtoupper($lifeDomain->life_rank) }}
                                </span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- ELITE SYSTEM SKILLS -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">
                <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                    <div class="w-10 h-10 rounded-lg bg-gold-rpg/10 border border-gold-rpg/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gold-rpg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h3 class="font-title text-lg font-black text-white tracking-wide">ELITE SYSTEM SKILLS</h3>
                        <p class="text-xs text-slate-400 font-bold tracking-wider">ACTIVE NEURO-PROFILE ATTRIBUTES</p>
                    </div>
                </div>

                @if ($skillsTrialExpired)
                    <div class="text-center py-10 bg-black/40 border border-red-500/20 rounded-xl space-y-4 p-6">
                        <div class="w-12 h-12 bg-red-500/10 border border-red-500/20 rounded-full flex items-center justify-center mx-auto text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h4 class="font-title font-black text-red-400 mt-2 tracking-wider">ELITE SYSTEM SKILLS LOCKED</h4>
                        <p class="text-xs text-gray-400 max-w-md mx-auto leading-relaxed">
                            Your 3-day trial has expired. To upgrade active neuro-profile skills and unlock elite S-Rank attribute boosts, pay 299 Rs via Razorpay / UPI.
                        </p>
                        <div class="pt-2">
                            <button 
                                wire:click="payForSkills"
                                class="px-8 py-3.5 bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue hover:scale-102 hover:opacity-95 transition duration-300"
                            >
                                UNLOCK ELITE SKILLS INDEFINITELY // 299 Rs
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach ($skills as $skill)
                            <div class="bg-black/40 border border-white/5 rounded-xl p-5 flex flex-col justify-between relative group hover:border-neon-purple/30 transition duration-300">
                                
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs text-neon-purple font-title font-black tracking-widest block uppercase">LEVEL {{ $skill->level }}</span>
                                        <h4 class="font-title text-sm font-black text-white mt-1">{{ strtoupper(explode(' (', $skill->skill_name)[0]) }}</h4>
                                    </div>

                                    @if ($user->skill_points > 0)
                                        <button 
                                            wire:click="spendSkillPoint({{ $skill->id }})"
                                            class="px-2.5 py-1.5 bg-neon-purple/20 border border-neon-purple/40 text-neon-purple hover:bg-neon-purple hover:text-white rounded text-xs font-title font-black tracking-wider transition"
                                            title="Spend 1 Skill Point"
                                        >
                                            + UPGRADE
                                        </button>
                                    @endif
                                </div>

                                <!-- Internal Subtracks details -->
                                <div class="mt-4 space-y-2 border-t border-white/5 pt-3">
                                    @if (is_array($skill->sub_tracks_scores))
                                        @foreach ($skill->sub_tracks_scores as $stName => $stVal)
                                            <div class="flex items-center justify-between text-xs text-slate-400 font-title font-bold">
                                                <span>{{ strtoupper($stName) }}</span>
                                                <span class="text-gray-300 font-black">{{ $stVal }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- RIGHT COLUMN: daily checklists, AI shadow coach -->
        <div class="lg:col-span-4 space-y-8">
            
            <!-- DAILY QUEST MATRIX -->
            <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">
                <div>
                    <h3 class="font-title text-base font-black text-white tracking-wide flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-neon-blue animate-ping"></span> DAILY QUEST PROTOCOL
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">COMPULSORY HABIT TARGETS // OR FACE STAT RESET</p>
                    </div>

                <div class="space-y-3">
                    @foreach ($dailyQuests as $quest)
                        <div 
                            wire:click="toggleQuest({{ $quest->id }})"
                            class="p-4 rounded-xl border transition cursor-pointer flex items-start gap-3
                            {{ $quest->is_completed ? 'bg-green-500/5 border-green-500/10 text-gray-500' : 'bg-black/50 border-white/5 hover:border-neon-blue/30 text-white' }}
                            "
                        >
                            <input 
                                type="checkbox" 
                                {{ $quest->is_completed ? 'checked' : '' }} 
                                disabled
                                class="rounded border border-white/30 bg-black/40 text-neon-blue focus:ring-0 w-4.5 h-4.5 mt-0.5 disabled:opacity-100"
                            >
                            <div class="space-y-1">
                                <div class="text-xs font-semibold leading-snug {{ $quest->is_completed ? 'line-through' : '' }}">{{ $quest->description }}</div>
                                <div class="text-xs font-title font-bold tracking-wider {{ $quest->is_completed ? 'text-green-400' : 'text-neon-blue' }}">
                                    {{ $quest->is_completed ? 'QUEST COMPLETED' : '+25 XP // +50 GOLD' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- AI SHADOW GUIDE TERMINAL -->
            <div class="bg-gradient-to-br from-obsidian-card to-[#090b12] border border-neon-purple/30 rounded-2xl p-6 shadow-xl relative overflow-hidden neon-border">
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(138,43,226,0.03)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center gap-2 border-b border-white/5 pb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        <span class="text-xs font-mono font-bold tracking-widest text-slate-400">SHADOW_GUIDE_COGNITIVE_MODULE</span>
                    </div>

                    <!-- Coaching msg stub -->
                    <div class="space-y-3 font-mono text-xs">
                        <div class="text-neon-purple font-semibold">&gt; Initializing neural connection...</div>
                        <div class="text-white bg-black/60 border border-white/5 rounded-lg p-4 italic leading-relaxed text-xs">
                            "{{ $aiCoachingMessage }}"
                        </div>
                    </div>

                    <!-- AI Weekly Growth report stub -->
                    <div class="space-y-4 font-mono text-xs border-t border-white/5 pt-4">
                        <div class="text-neon-blue font-semibold">&gt; Diagnostic Growth Analysis:</div>
                        <p class="text-gray-400 leading-normal text-xs">{{ $aiGrowthAssessment['assessment'] }}</p>
                        
                        <div class="space-y-1">
                            <span class="text-green-400 text-xs font-bold">REACTION STRENGTHS:</span>
                            @foreach ($aiGrowthAssessment['strengths'] as $s)
                                <div class="text-gray-300 pl-2 text-xs">- {{ $s }}</div>
                            @endforeach
                        </div>

                        <div class="space-y-1">
                            <span class="text-red-400 text-xs font-bold">FATIGUE VULNERABILITIES:</span>
                            @foreach ($aiGrowthAssessment['weaknesses'] as $w)
                                <div class="text-gray-300 pl-2 text-xs">- {{ $w }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- AWAKEN NEW CONTRACT MODAL -->
    @if ($showContractModal)
        <div class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="bg-obsidian-card border border-white/10 w-full max-w-lg rounded-2xl p-6 md:p-8 space-y-6 shadow-2xl relative neon-border">
                <div class="absolute top-2 left-2 w-3 h-3 border-t-2 border-l-2 border-neon-blue/40 pointer-events-none"></div>
                <div class="absolute top-2 right-2 w-3 h-3 border-t-2 border-r-2 border-neon-blue/40 pointer-events-none"></div>
                <div class="absolute bottom-2 left-2 w-3 h-3 border-b-2 border-l-2 border-neon-blue/40 pointer-events-none"></div>
                <div class="absolute bottom-2 right-2 w-3 h-3 border-b-2 border-r-2 border-neon-blue/40 pointer-events-none"></div>

                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <h3 class="font-title text-base font-black text-white tracking-widest">📜 AWAKEN A SYSTEM CONTRACT</h3>
                    <button wire:click="$set('showContractModal', false)" class="text-gray-500 hover:text-white font-title font-bold text-sm">✕</button>
                </div>

                <form wire:submit.prevent="acceptContract" class="space-y-4">
                    <div>
                        <label class="text-xs text-slate-400 font-title font-bold tracking-widest block mb-1">CONTRACT MISSION / HABIT TITLE</label>
                        <input 
                            type="text" 
                            wire:model="newContractTitle"
                            placeholder="e.g. Physical Awakening Routine"
                            class="w-full bg-black/40 border border-white/10 rounded-lg p-3 text-white focus:border-neon-blue focus:ring-1 focus:ring-neon-blue/20 outline-none text-sm font-semibold"
                        >
                        @error('newContractTitle') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-400 font-title font-bold tracking-widest block mb-1">DIFFICULTY RANK</label>
                            <select wire:model="newContractDifficulty" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 text-white focus:border-neon-blue outline-none text-sm font-semibold">
                                <option value="Easy">Easy</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard">Hard</option>
                                <option value="Elite">Elite</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 font-title font-bold tracking-widest block mb-1">CHRONO-DURATION</label>
                            <select wire:model="newContractDuration" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 text-white focus:border-neon-blue outline-none text-sm font-semibold">
                                <option value="7">7 Days</option>
                                <option value="21">21 Days</option>
                                <option value="51">51 Days</option>
                                <option value="71">71 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-black/60 rounded-xl p-4 border border-white/5 space-y-1">
                        <div class="text-xs text-slate-400 font-title font-black tracking-widest">SYSTEM REWARD PROJECTION:</div>
                        <div class="text-neon-blue font-title font-bold text-xs">
                            XP: +{{ intval($newContractDuration) * 20 * ['Easy'=>1,'Medium'=>2,'Hard'=>3,'Elite'=>5][$newContractDifficulty] }} XP
                        </div>
                        <div class="text-gold-rpg font-title font-bold text-xs">
                            GOLD: +{{ intval($newContractDuration) * 20 * ['Easy'=>1,'Medium'=>2,'Hard'=>3,'Elite'=>5][$newContractDifficulty] * 2 }} GOLD
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button 
                            type="button" 
                            wire:click="$set('showContractModal', false)"
                            class="px-4 py-2.5 border border-white/10 text-gray-400 font-title font-bold text-xs rounded-lg hover:border-white/20"
                        >
                            ABANDON
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-2.5 bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-title font-black text-xs rounded-lg shadow-neon-blue"
                        >
                            AWAKEN CONTRACT
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
