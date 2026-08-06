@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-title font-bold text-xs text-slate-400 tracking-widest uppercase mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
