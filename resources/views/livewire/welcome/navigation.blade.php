<nav class="flex items-center gap-4 font-title text-sm tracking-wider font-semibold">
    @auth
        <a
            href="{{ url('/dashboard') }}"
            class="px-5 py-2 rounded border border-neon-blue text-neon-blue hover:bg-neon-blue/10 transition shadow-neon-blue"
        >
            DASHBOARD
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="text-gray-400 hover:text-white transition"
        >
            LOGIN
        </a>

        @if (Route::has('register'))
            <a
                href="{{ route('register') }}"
                class="px-5 py-2.5 rounded bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-bold hover:opacity-90 transition shadow-neon-blue"
            >
                AWAKEN
            </a>
        @endif
    @endauth
</nav>

