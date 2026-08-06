@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-black/40 border border-white/10 text-white placeholder-slate-500 focus:border-neon-purple focus:ring-1 focus:ring-neon-purple/20 rounded-lg p-3 outline-none text-sm font-semibold transition duration-300 shadow-sm']) }}>
