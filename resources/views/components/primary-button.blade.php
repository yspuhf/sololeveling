<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue hover:scale-[1.02] hover:opacity-95 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-neon-purple/50 transition duration-300 uppercase']) }}>
    {{ $slot }}
</button>
