<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\SystemContract;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        $contracts = SystemContract::query()
            ->when($this->search, function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return [
            'contracts' => $contracts
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg">
        <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">⚔️ SYSTEM CONTRACTS MONITOR</h3>
        <p class="text-xs text-slate-500 mt-1">Audit active hunter training regimes, difficulty distributions, and check-in rates.</p>
    </div>

    <!-- Search Controls -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-4 shadow-lg">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search contracts by hunter name, email, or contract title..." 
            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
        >
    </div>

    <!-- Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4">ID</th>
                        <th class="p-4">HUNTER</th>
                        <th class="p-4">CONTRACT OBJECTIVE</th>
                        <th class="p-4">DIFFICULTY / DURATION</th>
                        <th class="p-4">STATUS</th>
                        <th class="p-4">ACTIVATED AT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-mono font-bold text-slate-400">#{{ $contract->id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $contract->user->name ?? 'Unknown Hunter' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $contract->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4 font-bold text-white">{{ $contract->title }}</td>
                            <td class="p-4">
                                <span class="font-title font-bold text-neon-blue uppercase">{{ $contract->difficulty }}</span>
                                <span class="text-[10px] text-slate-500 font-mono block">{{ $contract->duration_days }} Days regime</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase
                                    @if($contract->status === 'active') bg-green-500/10 text-green-400 border border-green-500/20
                                    @elseif($contract->status === 'completed') bg-blue-500/10 text-blue-400 border border-blue-500/20
                                    @else bg-gray-500/10 text-gray-400 border border-gray-500/20 @endif">
                                    {{ $contract->status }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ $contract->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-title font-bold tracking-wider">
                                NO REGISTERED HABIT TIMELINE CONTRACTS
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="p-4 border-t border-white/5 bg-black/10">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
