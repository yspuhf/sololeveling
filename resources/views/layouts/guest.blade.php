<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ARISE') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.theme === 'light') {
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.remove('light');
            }
            function toggleTheme() {
                if (document.documentElement.classList.contains('light')) {
                    document.documentElement.classList.remove('light');
                    localStorage.theme = 'dark';
                } else {
                    document.documentElement.classList.add('light');
                    localStorage.theme = 'light';
                }
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: localStorage.theme }));
            }
        </script>
        <style>
            body {
                font-family: 'Outfit', sans-serif;
                background-color: #1e293b;
            }
            .font-title {
                font-family: 'Orbitron', sans-serif;
            }
            @keyframes border-glow {
                0%, 100% {
                    box-shadow: 0 0 15px rgba(138, 43, 226, 0.2), inset 0 0 10px rgba(138, 43, 226, 0.05);
                    border-color: rgba(138, 43, 226, 0.3);
                }
                50% {
                    box-shadow: 0 0 25px rgba(138, 43, 226, 0.4), inset 0 0 15px rgba(138, 43, 226, 0.15);
                    border-color: rgba(138, 43, 226, 0.6);
                }
            }
            .neon-card-glow {
                animation: border-glow 5s infinite alternate;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-obsidian-dark text-gray-300">
        <!-- Theme Toggle Button -->
        <div class="fixed top-6 right-6 z-50">
            <button 
                onclick="toggleTheme()" 
                class="p-2.5 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:border-neon-blue/30 bg-black/20 hover:bg-black/35 backdrop-blur-md transition duration-300 flex items-center justify-center shadow-lg"
                title="Toggle Day/Night Mode"
            >
                <svg class="w-5 h-5 hidden html-light-sun" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707-.707m12.728 12.728l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <svg class="w-5 h-5 block html-dark-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>

        <!-- Sci-Fi background Grid lines -->
        <div class="fixed inset-0 bg-[linear-gradient(to_right,var(--obsidian-light)_1px,transparent_1px),linear-gradient(to_bottom,var(--obsidian-light)_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] pointer-events-none z-0 opacity-60"></div>
        <div class="fixed top-[20%] left-[30%] w-[500px] h-[500px] bg-neon-purple/5 rounded-full blur-[100px] pointer-events-none z-0"></div>

        <div class="relative z-10 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Glowing Gradient Logo -->
            <div class="mb-6">
                <a href="/" wire:navigate class="flex flex-col items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-neon-blue via-neon-purple to-gold-rpg flex items-center justify-center font-bold text-obsidian-dark font-title text-2xl tracking-wider shadow-neon-blue">
                        Λ
                    </div>
                    <span class="font-title text-2xl font-black text-white tracking-widest">ΛRISE</span>
                </a>
            </div>

            <!-- Styled holographic form container -->
            <div class="w-full sm:max-w-md px-8 py-8 bg-obsidian-card border border-white/10 rounded-2xl shadow-2xl relative overflow-hidden neon-card-glow">
                
                <!-- HUD Corner Marks -->
                <div class="absolute top-2 left-2 w-3 h-3 border-t-2 border-l-2 border-neon-purple/40 pointer-events-none"></div>
                <div class="absolute top-2 right-2 w-3 h-3 border-t-2 border-r-2 border-neon-purple/40 pointer-events-none"></div>
                <div class="absolute bottom-2 left-2 w-3 h-3 border-b-2 border-l-2 border-neon-purple/40 pointer-events-none"></div>
                <div class="absolute bottom-2 right-2 w-3 h-3 border-b-2 border-r-2 border-neon-purple/40 pointer-events-none"></div>

                <!-- CRT scanline overlay details -->
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(138,43,226,0.02)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
