<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Response;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $rankFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'statusFilter', 'rankFilter', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function exportCsv()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=hunters_export_" . now()->format('YmdHis') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $callback = function() use($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Level', 'XP', 'Rank', 'Status', 'Registered At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->level,
                    $user->xp,
                    $user->determineRank(),
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function with()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return [
            'users' => $users
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Controls Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">HUNTERS ROSTER SEARCH</h3>
            <div class="flex items-center gap-3">
                <button wire:click="exportCsv" class="px-4 py-2 border border-white/10 hover:border-white/20 text-slate-300 hover:text-white font-title font-bold text-xs tracking-widest rounded-xl transition duration-300">
                    📤 EXPORT CSV
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search field -->
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by name or email..." 
                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <select 
                    wire:model.live="statusFilter" 
                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-slate-300 focus:border-neon-purple focus:ring-0 transition"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="pending_verification">Pending Verification</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <!-- Clear filters -->
            <div class="flex items-center justify-end">
                @if($search || $statusFilter)
                    <button wire:click="$set('search', ''); $set('statusFilter', '');" class="text-xs text-neon-purple hover:underline font-title font-bold tracking-wider">
                        RESET FILTERS
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4 cursor-pointer hover:text-white transition" wire:click="sortBy('id')">
                            ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '' !!}
                        </th>
                        <th class="p-4 cursor-pointer hover:text-white transition" wire:click="sortBy('name')">
                            HUNTER NAME {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '' !!}
                        </th>
                        <th class="p-4 cursor-pointer hover:text-white transition" wire:click="sortBy('email')">
                            EMAIL ADDRESS {!! $sortField === 'email' ? ($sortDirection === 'asc' ? '▲' : '▼') : '' !!}
                        </th>
                        <th class="p-4 cursor-pointer hover:text-white transition" wire:click="sortBy('level')">
                            LVL / XP {!! $sortField === 'level' ? ($sortDirection === 'asc' ? '▲' : '▼') : '' !!}
                        </th>
                        <th class="p-4">SYSTEM RANK</th>
                        <th class="p-4">STATUS</th>
                        <th class="p-4 cursor-pointer hover:text-white transition" wire:click="sortBy('created_at')">
                            REGISTERED AT {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '' !!}
                        </th>
                        <th class="p-4 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-mono font-bold text-slate-400">#{{ $user->id }}</td>
                            <td class="p-4 font-bold text-white">{{ $user->name }}</td>
                            <td class="p-4 font-mono">{{ $user->email }}</td>
                            <td class="p-4">
                                <span class="font-title font-black text-neon-blue">Lvl {{ $user->level }}</span>
                                <span class="text-[10px] text-slate-500 font-mono block">{{ $user->xp }} XP</span>
                            </td>
                            <td class="p-4">
                                @php $rank = $user->determineRank(); @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-title font-black tracking-widest uppercase
                                    @if(in_array($rank, ['S-Rank', 'National Rank', 'Monarch Rank'])) bg-gold-rpg/10 text-gold-rpg border border-gold-rpg/20
                                    @elseif($rank === 'A-Rank') bg-neon-purple/10 text-neon-purple border border-neon-purple/20
                                    @elseif($rank === 'B-Rank') bg-neon-blue/10 text-neon-blue border border-neon-blue/20
                                    @else bg-slate-500/10 text-slate-400 border border-slate-500/20 @endif">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase
                                    @if($user->status === 'active') bg-green-500/10 text-green-400 border border-green-500/20
                                    @elseif($user->status === 'suspended') bg-red-500/10 text-red-400 border border-red-500/20
                                    @else bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 @endif">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="px-3.5 py-1.5 bg-neon-purple/10 hover:bg-neon-purple/20 text-white font-title font-bold text-[10px] tracking-wider rounded-lg transition border border-neon-purple/20">
                                    MANAGE
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 font-title font-bold tracking-wider">
                                NO HUNTERS FOUND MATCHING CRITERIA
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-white/5 bg-black/10">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
