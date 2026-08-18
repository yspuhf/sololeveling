<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Subscription;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        $subscriptions = Subscription::query()
            ->when($this->search, function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return [
            'subscriptions' => $subscriptions
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg">
        <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">HUNTER MEMBERSHIPS & SUBSCRIPTIONS</h3>
        <p class="text-xs text-slate-500 mt-1">View active licenses, renewal timelines, and expired entitlements.</p>
    </div>

    <!-- Search Controls -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-4 shadow-lg">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search subscriptions by hunter name or email..." 
            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
        >
    </div>

    <!-- Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4">ID</th>
                        <th class="p-4">HUNTER</th>
                        <th class="p-4">PLAN NAME</th>
                        <th class="p-4">STATUS</th>
                        <th class="p-4">ACTIVATED AT</th>
                        <th class="p-4">EXPIRATION AT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-mono font-bold text-slate-400">#{{ $subscription->id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $subscription->user->name ?? 'Unknown Hunter' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $subscription->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4 font-title font-black text-neon-blue uppercase">{{ $subscription->plan->name ?? 'Deleted Plan' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase
                                    @if($subscription->status === 'active') bg-green-500/10 text-green-400 border border-green-500/20
                                    @elseif($subscription->status === 'expired') bg-red-500/10 text-red-400 border border-red-500/20
                                    @else bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 @endif">
                                    {{ $subscription->status }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ $subscription->started_at->format('Y-m-d H:i') }}</td>
                            <td class="p-4 font-mono text-slate-400">{{ $subscription->expires_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-title font-bold tracking-wider">
                                NO SUBSCRIPTIONS CONFIGURED
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="p-4 border-t border-white/5 bg-black/10">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
