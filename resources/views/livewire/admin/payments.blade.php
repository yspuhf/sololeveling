<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Payment;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=transactions_export_" . now()->format('YmdHis') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $payments = Payment::query()
            ->when($this->search, function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function() use($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Hunter Name', 'Email', 'Plan ID', 'Transaction ID', 'Amount', 'Currency', 'Gateway', 'Status', 'Paid At']);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->user->name ?? 'Deleted Hunter',
                    $payment->user->email ?? 'N/A',
                    $payment->plan_id ?? 'N/A',
                    $payment->transaction_id,
                    $payment->amount,
                    $payment->currency,
                    $payment->gateway,
                    $payment->status,
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function with()
    {
        $payments = Payment::query()
            ->when($this->search, function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return [
            'payments' => $payments
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-6 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <h3 class="font-title text-sm font-black text-white tracking-widest uppercase">TRANSACTION LOG METRICS</h3>
            <p class="text-xs text-slate-500">View and audit all hunter payment records in real-time.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="exportCsv" class="px-4 py-2 border border-white/10 hover:border-white/20 text-slate-300 hover:text-white font-title font-bold text-xs tracking-widest rounded-xl transition duration-300">
                📤 EXPORT CSV
            </button>
        </div>
    </div>

    <!-- Search Controls -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl p-4 shadow-lg">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by hunter name, email, or transaction ID..." 
            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-neon-purple focus:ring-0 transition"
        >
    </div>

    <!-- Table -->
    <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-black/20 border-b border-white/5 font-title font-bold text-slate-400 tracking-wider">
                        <th class="p-4">TX ID</th>
                        <th class="p-4">HUNTER</th>
                        <th class="p-4">GATEWAY</th>
                        <th class="p-4">AMOUNT</th>
                        <th class="p-4">STATUS</th>
                        <th class="p-4">DATE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-4 font-mono font-bold text-slate-400">{{ $payment->transaction_id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $payment->user->name ?? 'Unknown Hunter' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $payment->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ $payment->gateway }}</td>
                            <td class="p-4 font-title font-black text-green-400">₹{{ number_format($payment->amount) }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-title font-bold tracking-widest uppercase
                                    @if($payment->status === 'successful') bg-green-500/10 text-green-400 border border-green-500/20
                                    @elseif($payment->status === 'failed') bg-red-500/10 text-red-400 border border-red-500/20
                                    @else bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 @endif">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-400">
                                {{ $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i') : $payment->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-title font-bold tracking-wider">
                                NO TRANSACTIONS RECORDED
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-white/5 bg-black/10">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
