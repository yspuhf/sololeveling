<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\AdminAuditLog;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        $logs = AdminAuditLog::query()
            ->when($this->search, function ($query) {
                $query->where('action', 'like', '%' . $this->search . '%')
                      ->orWhere('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('admin', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return [
            'logs' => $logs
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg">
        <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">📜 SYSTEM AUDIT CONSOLE</h3>
        <p class="text-xs text-slate-500 mt-1">Audit log repository recording all administrator actions, feature overrides, and config shifts.</p>
    </div>

    <!-- Search Controls -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-4 shadow-lg">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search audit logs by admin name, action, or reason details..." 
            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
        >
    </div>

    <!-- Logs Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4">DATE</th>
                        <th class="p-4">ADMINISTRATOR</th>
                        <th class="p-4">ACTION</th>
                        <th class="p-4">AFFECTED ID</th>
                        <th class="p-4">REASON DETAILS</th>
                        <th class="p-4">IP ADDRESS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-mono text-slate-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="p-4 font-bold text-white">{{ $log->admin->name ?? 'System' }}</td>
                            <td class="p-4 font-mono text-neon-blue uppercase">{{ $log->action }}</td>
                            <td class="p-4 font-mono text-slate-400">#{{ $log->target_id ?: 'N/A' }} ({{ $log->target_type ?: 'System' }})</td>
                            <td class="p-4">
                                <div class="text-slate-300 font-semibold">{{ $log->reason }}</div>
                                @if($log->old_value || $log->new_value)
                                    <div class="text-[9px] text-slate-500 font-mono mt-0.5 truncate max-w-sm">
                                        Shift: {{ $log->old_value }} ➡️ {{ $log->new_value }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-slate-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-title font-bold tracking-wider">
                                NO AUDIT RECORDS FOUND MATCHING CRITERIA
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-white/5 bg-black/10">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
