<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ARISE — The Human Evolution System</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

        <!-- Styles & Script -->
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
            /* Custom CSS Animations */
            @keyframes pulse-neon {
                0%, 100% {
                    text-shadow: 0 0 10px rgba(69, 243, 255, 0.5), 0 0 20px rgba(69, 243, 255, 0.3), 0 0 40px rgba(69, 243, 255, 0.1);
                    filter: drop-shadow(0 0 5px rgba(69, 243, 255, 0.3));
                }
                50% {
                    text-shadow: 0 0 15px rgba(69, 243, 255, 0.8), 0 0 30px rgba(69, 243, 255, 0.6), 0 0 60px rgba(69, 243, 255, 0.3);
                    filter: drop-shadow(0 0 10px rgba(69, 243, 255, 0.5));
                }
            }
            .animate-neon {
                animation: pulse-neon 3s infinite alternate;
            }
            @keyframes hud-grid-scan {
                0% { background-position: 0 0; }
                100% { background-position: 0 100%; }
            }
            .hud-scanlines {
                position: relative;
            }
            .hud-scanlines::after {
                content: " ";
                display: block;
                position: absolute;
                top: 0; left: 0; bottom: 0; right: 0;
                background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
                z-index: 2;
                background-size: 100% 4px, 6px 100%;
                pointer-events: none;
            }
            @keyframes border-rotate {
                0% { border-color: rgba(69, 243, 255, 0.3); box-shadow: 0 0 10px rgba(69, 243, 255, 0.1); }
                50% { border-color: rgba(138, 43, 226, 0.6); box-shadow: 0 0 20px rgba(138, 43, 226, 0.3); }
                100% { border-color: rgba(69, 243, 255, 0.3); box-shadow: 0 0 10px rgba(69, 243, 255, 0.1); }
            }
            .hud-card {
                border: 1px solid rgba(255, 255, 255, 0.05);
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .hud-card:hover {
                border-color: rgba(69, 243, 255, 0.3);
                box-shadow: 0 10px 30px -10px rgba(69, 243, 255, 0.15);
                transform: translateY(-4px);
            }
            .glow-btn {
                position: relative;
                overflow: hidden;
            }
            .glow-btn::before {
                content: '';
                position: absolute;
                top: 0; left: -100%; width: 100%; height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                transition: 0.5s;
            }
            .glow-btn:hover::before {
                left: 100%;
            }
            /* Breathing float effect for hero image */
            @keyframes hero-float {
                0%, 100% {
                    transform: scale(1.05) translateY(0px) rotate(0deg);
                    filter: contrast(1.1) brightness(0.95);
                }
                50% {
                    transform: scale(1.08) translateY(-6px) rotate(0.5deg);
                    filter: contrast(1.15) brightness(1.05);
                }
            }
            .animate-hero-float {
                animation: hero-float 6s ease-in-out infinite;
            }
            /* Moving laser scanner line */
            @keyframes scanline-move {
                0% {
                    top: 0%;
                    opacity: 0;
                }
                10%, 90% {
                    opacity: 1;
                }
                100% {
                    top: 100%;
                    opacity: 0;
                }
            }
            .laser-scanner {
                position: absolute;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, transparent, #45f3ff, #45f3ff, transparent);
                box-shadow: 0 0 10px #45f3ff, 0 0 20px #45f3ff;
                z-index: 15;
                pointer-events: none;
                animation: scanline-move 4s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="antialiased text-gray-300 overflow-x-hidden selection:bg-neon-blue selection:text-obsidian-dark">

        <!-- Sci-Fi background Grid lines -->
        <div class="fixed inset-0 bg-[linear-gradient(to_right,var(--obsidian-light)_1px,transparent_1px),linear-gradient(to_bottom,var(--obsidian-light)_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] pointer-events-none z-0"></div>

        <!-- Ambient blur spots -->
        <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-neon-blue/5 rounded-full blur-[160px] pointer-events-none z-0"></div>
        <div class="fixed bottom-[-10%] right-[-10%] w-[900px] h-[900px] bg-neon-purple/5 rounded-full blur-[180px] pointer-events-none z-0"></div>

        <div class="relative z-10 min-h-screen flex flex-col justify-between">
            <!-- Transparent Header/Navbar -->
            <header x-data="{ mobileMenuOpen: false }" class="border-b border-white/5 bg-black/60 backdrop-blur-xl sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded bg-gradient-to-br from-neon-blue via-neon-purple to-gold-rpg flex items-center justify-center font-bold text-obsidian-dark font-title text-xl tracking-wider shadow-neon-blue">
                            Λ
                        </div>
                        <span class="font-title text-2xl font-black text-white tracking-widest bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">
                            ΛRISE
                        </span>
                    </div>

                    <nav class="hidden md:flex items-center gap-8 font-title text-xs tracking-widest font-bold text-gray-400">
                        <a href="#evolution" class="hover:text-neon-blue transition duration-300">SYSTEM PROGRESSION</a>
                        <a href="#skills" class="hover:text-neon-blue transition duration-300">ELITE SKILLS</a>
                        <a href="#leaderboard" class="hover:text-neon-blue transition duration-300">LEADERBOARD</a>
                        <a href="#shadow-guide" class="hover:text-neon-blue transition duration-300">SHADOW COACH</a>
                    </nav>

                    <div class="flex items-center gap-4">
                        <!-- Theme Toggle Button -->
                        <button 
                            onclick="toggleTheme()" 
                            class="p-2.5 rounded-lg border border-white/5 text-slate-400 hover:text-white hover:border-neon-blue/35 bg-black/10 hover:bg-black/20 transition duration-300 flex items-center justify-center"
                            title="Toggle brightness setting"
                        >
                            <svg class="w-4 h-4 hidden html-light-sun" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707-.707m12.728 12.728l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            <svg class="w-4 h-4 block html-dark-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <div class="hidden md:flex items-center gap-4">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="font-title px-6 py-2.5 rounded bg-transparent border border-neon-blue text-neon-blue hover:bg-neon-blue/10 transition text-xs tracking-widest font-black shadow-neon-blue glow-btn">
                                        ENTER SYSTEM
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="font-title text-xs tracking-widest font-bold text-gray-400 hover:text-white transition">
                                        LOGIN
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="font-title px-6 py-3 rounded bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-black text-xs tracking-widest hover:scale-105 transition duration-300 shadow-neon-blue glow-btn">
                                            AWAKEN NOW
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>

                        <!-- Mobile hamburger toggler -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2.5 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:border-neon-blue/35 block md:hidden bg-black/20 focus:outline-none transition duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Responsive Mobile Menu Dropdown -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 -translate-y-4" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     x-transition:leave="transition ease-in duration-150" 
                     x-transition:leave-start="opacity-100 translate-y-0" 
                     x-transition:leave-end="opacity-0 -translate-y-4" 
                     class="md:hidden border-t border-white/5 bg-obsidian-card px-6 py-4 space-y-4 shadow-2xl relative z-40"
                     style="display: none;"
                >
                    <nav class="flex flex-col gap-4 font-title text-xs tracking-widest font-bold text-slate-400">
                        <a href="#evolution" @click="mobileMenuOpen = false" class="hover:text-neon-blue transition duration-300">SYSTEM PROGRESSION</a>
                        <a href="#skills" @click="mobileMenuOpen = false" class="hover:text-neon-blue transition duration-300">ELITE SKILLS</a>
                        <a href="#leaderboard" @click="mobileMenuOpen = false" class="hover:text-neon-blue transition duration-300">LEADERBOARD</a>
                        <a href="#shadow-guide" @click="mobileMenuOpen = false" class="hover:text-neon-blue transition duration-300">SHADOW COACH</a>
                    </nav>
                    <hr class="border-white/5">
                    <div class="flex flex-col gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" @click="mobileMenuOpen = false" class="font-title px-6 py-3 rounded bg-transparent border border-neon-blue text-neon-blue text-center text-xs tracking-widest font-black shadow-neon-blue glow-btn">
                                    ENTER SYSTEM
                                </a>
                            @else
                                <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="font-title text-xs tracking-widest font-bold text-center text-gray-400 hover:text-white transition py-2">
                                    LOGIN
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="font-title px-6 py-3 rounded bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark text-center font-black text-xs tracking-widest hover:opacity-95 transition shadow-neon-blue glow-btn">
                                        AWAKEN NOW
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            <main class="flex-grow">
                <!-- HERO SECTION -->
                <section class="max-w-7xl mx-auto px-6 py-12 lg:py-24 grid lg:grid-cols-12 gap-12 items-center relative">
                    <!-- Left Hero Content -->
                    <div class="lg:col-span-7 space-y-8 text-left">
                        <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full border border-neon-blue/30 bg-neon-blue/5 text-neon-blue text-[10px] font-title tracking-widest font-black">
                            <span class="w-2 h-2 rounded-full bg-neon-blue animate-pulse"></span>
                            SYSTEM DIRECTIVE: HUMAN AWAKENING PROGRAM
                        </div>

                        <h1 class="font-title text-6xl md:text-7xl lg:text-8xl font-black text-white tracking-tighter leading-none">
                            CONQUER YOUR <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg animate-neon">LIMITS</span>
                        </h1>

                        <p class="max-w-xl text-lg text-gray-400 font-light leading-relaxed">
                            Arise translates your daily fitness, mental health, career, and financial targets into a high-stakes RPG. Upgrade your stats, unlock S-Rank skills, join guilds, and conquer consistency.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <a href="{{ route('register') }}" class="font-title px-8 py-4 rounded bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-black tracking-widest text-sm shadow-neon-blue hover:scale-105 transition duration-300 text-center glow-btn">
                                ACCEPT THE CONTRACT
                            </a>
                            <a href="#evolution" class="font-title px-8 py-4 rounded bg-obsidian-card border border-white/10 hover:border-neon-blue/30 hover:text-white text-gray-400 tracking-widest text-sm transition duration-300 text-center">
                                ANALYZE PARAMETERS
                            </a>
                        </div>
                    </div>

                    <!-- Right Hero Graphic: Protagonist Avatar with HUD frame -->
                    <div class="lg:col-span-5 relative flex justify-center items-center">
                        <div class="w-full max-w-[420px] aspect-[4/5] rounded-3xl border border-neon-blue/20 bg-obsidian-card p-4 relative shadow-2xl overflow-hidden group">
                            
                            <!-- Sci-fi Corner Marks -->
                            <div class="absolute top-2 left-2 w-4 h-4 border-t-2 border-l-2 border-neon-blue pointer-events-none"></div>
                            <div class="absolute top-2 right-2 w-4 h-4 border-t-2 border-r-2 border-neon-blue pointer-events-none"></div>
                            <div class="absolute bottom-2 left-2 w-4 h-4 border-b-2 border-l-2 border-neon-blue pointer-events-none"></div>
                            <div class="absolute bottom-2 right-2 w-4 h-4 border-b-2 border-r-2 border-neon-blue pointer-events-none"></div>

                            <!-- Inner HUD scanning overlay -->
                            <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(69,243,255,0.08)_2px,transparent_2px)] bg-[size:100%_12px] z-10 pointer-events-none animate-pulse"></div>

                            <div class="relative w-full h-full rounded-2xl overflow-hidden bg-black/40">
                                <!-- Laser scanning line -->
                                <div class="laser-scanner"></div>
                                <img 
                                    src="{{ asset('images/hunter_hero.png') }}" 
                                    alt="Awakened Hunter Protagonist" 
                                    class="w-full h-full object-cover animate-hero-float"
                                >
                                <!-- Dark/Neon aura overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-obsidian-dark via-transparent to-transparent opacity-80"></div>
                                
                                <!-- Floating Status Bar -->
                                <div class="absolute bottom-6 left-6 right-6 bg-black/70 border border-white/10 backdrop-blur-md p-4 rounded-xl space-y-2 z-20">
                                    <div class="flex justify-between items-center text-[10px] font-title font-black tracking-widest text-neon-blue">
                                        <span>STATUS: AWAKENED</span>
                                        <span>MONARCH LEVEL</span>
                                    </div>
                                    <div class="w-full bg-white/10 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-neon-blue to-neon-purple h-full rounded-full" style="width: 82%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- EVOLUTION JOURNEY -->
                <section id="evolution" class="border-t border-white/5 bg-black/30 py-24">
                    <div class="max-w-7xl mx-auto px-6">
                        <div class="text-center mb-16 space-y-4">
                            <h2 class="font-title text-3xl md:text-5xl font-black text-white tracking-wide">
                                THE EVOLUTION RANK MATRIX
                            </h2>
                            <div class="w-24 h-1 bg-gradient-to-r from-neon-blue to-neon-purple mx-auto rounded-full"></div>
                            <p class="text-gray-400 max-w-xl mx-auto text-sm md:text-base">
                                Ascent depends on daily ritual execution. Ascend your tier standing, level up, and unlock special class privileges.
                            </p>
                        </div>

                        <!-- Timeline steps using Alpine.js for interactivity -->
                        <div x-data="{ activeRank: 'E-Rank' }" class="grid lg:grid-cols-12 gap-8 items-center">
                            <!-- Left: list of ranks -->
                            <div class="lg:col-span-5 space-y-2">
                                @php
                                    $ranks = [
                                        'E-Rank' => ['desc' => 'The weakest Hunter tier. Struggling to build core physical habits, sleep schedules, and basic nutrition.', 'level' => '1 - 10', 'badge' => 'E'],
                                        'D-Rank' => ['desc' => 'Routine check-in habit established. Starting consistent hydration and light activity parameters.', 'level' => '11 - 20', 'badge' => 'D'],
                                        'C-Rank' => ['desc' => 'Advanced habit retention forms. Emotional control indices stabilizes, and project backlogs clear.', 'level' => '21 - 35', 'badge' => 'C'],
                                        'B-Rank' => ['desc' => 'An elite. Demonstrating advanced skill upgrades in retention, mental capacity, and finance optimization.', 'level' => '36 - 50', 'badge' => 'B'],
                                        'A-Rank' => ['desc' => 'Top tier status. Running multi-task challenges and keeping active streaks across all 6 Life Domains.', 'level' => '51 - 70', 'badge' => 'A'],
                                        'S-Rank' => ['desc' => 'National scale power. Absolute mastery over focus control, high intensity tasks, and flow state duration.', 'level' => '71 - 90', 'badge' => 'S'],
                                        'National Rank' => ['desc' => 'Reserved for global leaders. Absolute consistency over months, unlocking elite coaching status.', 'level' => '91 - 99', 'badge' => 'NAT'],
                                        'Monarch Rank' => ['desc' => 'Ascended beyond biological capacity. The absolute evolution of human capability and mental supremacy.', 'level' => '100+', 'badge' => 'MON'],
                                    ];
                                @endphp

                                @foreach ($ranks as $name => $info)
                                    <button 
                                        @click="activeRank = '{{ $name }}'"
                                        :class="activeRank === '{{ $name }}' ? 'bg-gradient-to-r from-neon-blue/10 to-neon-purple/10 border-neon-blue text-white shadow-neon-blue' : 'bg-obsidian-card border-white/5 text-gray-500 hover:text-gray-300 hover:border-white/10'"
                                        class="w-full text-left px-5 py-4 rounded-xl border transition duration-300 flex items-center justify-between group"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div 
                                                :class="activeRank === '{{ $name }}' ? 'bg-neon-blue text-obsidian-dark font-black shadow-neon-blue' : 'bg-white/5 text-gray-500'" 
                                                class="w-8 h-8 rounded flex items-center justify-center font-title font-bold text-xs"
                                            >
                                                {{ $info['badge'] }}
                                            </div>
                                            <span class="font-title font-black text-lg tracking-wider">{{ $name }}</span>
                                        </div>
                                        <span class="font-title font-bold text-xs text-neon-blue tracking-wide">
                                            Lv. {{ $info['level'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <!-- Right: Rank Details Card -->
                            <div class="lg:col-span-7 h-full flex flex-col justify-center">
                                @foreach ($ranks as $name => $info)
                                    <div 
                                        x-show="activeRank === '{{ $name }}'" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                        class="bg-obsidian-card border border-white/10 rounded-2xl p-8 lg:p-12 shadow-2xl relative overflow-hidden hud-scanlines"
                                    >
                                        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-neon-purple/10 to-transparent rounded-full pointer-events-none"></div>
                                        
                                        <div class="text-gold-rpg font-title text-xs tracking-widest font-black mb-6 uppercase flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gold-rpg animate-ping"></span>
                                            SYSTEM DATA // HUNTER PROGRESSION
                                        </div>

                                        <h3 class="font-title text-4xl lg:text-5xl font-black text-white tracking-wide mb-6">
                                            {{ $name }}
                                        </h3>

                                        <div class="bg-black/60 rounded-xl p-6 border border-white/5 mb-8">
                                            <p class="text-gray-300 text-base md:text-lg leading-relaxed font-light">
                                                {{ $info['desc'] }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-6 border-t border-white/5 pt-8">
                                            <div>
                                                <div class="text-gray-500 text-[10px] font-title tracking-widest mb-1">XP REQUIREMENT</div>
                                                <div class="text-neon-blue font-title font-black text-2xl">
                                                    {{ str_contains($info['level'], '+') ? '10,000+ XP' : (intval(explode(' - ', $info['level'])[1]) * 100) . ' XP' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-gray-500 text-[10px] font-title tracking-widest mb-1">STATUS POWER MULTIPLIER</div>
                                                <div class="text-neon-purple font-title font-black text-2xl">
                                                    {{ str_contains($name, 'Monarch') ? 'x10.0 Power' : 'x' . (1 + intval(explode(' - ', $info['level'])[0]) * 0.1) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <!-- POWER ASSESSMENT & SKILL TREE PREVIEW -->
                <section id="skills" class="py-24 max-w-7xl mx-auto px-6">
                    <div class="text-center mb-16 space-y-4">
                        <h2 class="font-title text-3xl md:text-5xl font-black text-white tracking-wide">
                            ELITE SKILLS INTERFACE
                        </h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-neon-blue to-neon-purple mx-auto rounded-full"></div>
                        <p class="text-gray-400 max-w-xl mx-auto text-sm md:text-base">
                            spend skill points earned during level-ups to customize your active neuro-profile stats.
                        </p>
                    </div>

                    <div x-data="{ activeSkill: 'Emotional Management' }" class="grid md:grid-cols-12 gap-8 items-start">
                        <!-- Skill Description (Center/Left) -->
                        <div class="md:col-span-8 bg-obsidian-card border border-white/5 rounded-2xl p-8 relative overflow-hidden h-full min-h-[420px] flex flex-col justify-between hud-scanlines">
                            @php
                                $skills = [
                                    'Emotional Management' => [
                                        'sub' => 'Shadow Track',
                                        'grade' => 'Special S-Rank Class',
                                        'description' => 'Unlocks the ability to stay calm and execute logic in high-stress situations. Suppresses panic, triggers focus mode, and stabilizes mental well-being.',
                                        'tracks' => ['Stress Resilience', 'Stoicism Ratio', 'Heart-Rate Control'],
                                        'icon' => '<svg class="w-6 h-6 text-neon-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>'
                                    ],
                                    'Innovative Thinking' => [
                                        'sub' => 'Elite Track',
                                        'grade' => 'A-Rank Intelligence Class',
                                        'description' => 'Triggers dynamic pattern matching, enabling quick pivots in career and projects. Increases intellectual productivity.',
                                        'tracks' => ['Creative Fluency', 'Lateral Leap', 'Divergent Problem Solving'],
                                        'icon' => '<svg class="w-6 h-6 text-neon-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>'
                                    ],
                                    'Super Memory' => [
                                        'sub' => 'Rare Track',
                                        'grade' => 'B-Rank Retentive Class',
                                        'description' => 'Boosts speed-reading, retention of technical documents, and language learning capabilities.',
                                        'tracks' => ['Anki Recall', 'Mind Palace Nodes', 'Active Recall Frequency'],
                                        'icon' => '<svg class="w-6 h-6 text-gold-rpg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
                                    ],
                                    'Heightened Sensory' => [
                                        'sub' => 'Awakened Track',
                                        'grade' => 'A-Rank Perception Class',
                                        'description' => 'Increases environmental awareness, deep focus, and nutritional mindfulness. Prompts immediate warnings to bad habits.',
                                        'tracks' => ['Habit Cue Detection', 'Distraction Immunity', 'Flow State Entrance Speed'],
                                        'icon' => '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                                    ],
                                    'Multi-Tasking' => [
                                        'sub' => 'Epic Track',
                                        'grade' => 'S-Rank Speed Class',
                                        'description' => 'Allows seamless switching between complex operations without context-switching decay. Optimizes time efficiency.',
                                        'tracks' => ['Parallel Context Switching', 'Focus Stacking', 'Executive Functioning Rate'],
                                        'icon' => '<svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>'
                                    ],
                                    'General Mind Stimulation' => [
                                        'sub' => 'Legendary Track',
                                        'grade' => 'SS-Rank Overlord Class',
                                        'description' => 'The ultimate mental overdrive. Enhances overall brain stimulation, neurotransmitter health, and active cognition.',
                                        'tracks' => ['Neurogenesis Index', 'Synaptic Processing Speed', 'Mental Stamina Limit'],
                                        'icon' => '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                                    ],
                                ];
                            @endphp

                            @foreach ($skills as $name => $details)
                                <div 
                                    x-show="activeSkill === '{{ $name }}'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="space-y-6"
                                >
                                    <div class="flex items-center justify-between border-b border-white/5 pb-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center">
                                                {!! $details['icon'] !!}
                                            </div>
                                            <div>
                                                <span class="text-neon-purple text-[10px] font-title font-black tracking-widest uppercase block mb-1">
                                                    {{ $details['sub'] }}
                                                </span>
                                                <h3 class="font-title text-2xl font-black text-white">{!! $name !!}</h3>
                                            </div>
                                        </div>
                                        <div class="px-3 py-1 rounded bg-neon-blue/10 border border-neon-blue/20 text-neon-blue text-[10px] font-title font-black tracking-wider">
                                            {{ $details['grade'] }}
                                        </div>
                                    </div>

                                    <p class="text-gray-300 text-base leading-relaxed font-light">
                                        {{ $details['description'] }}
                                    </p>

                                    <div class="space-y-4">
                                        <h4 class="font-title text-xs tracking-wider font-bold text-gray-500">INTERNAL SUB-TRACK SCORING:</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            @foreach ($details['tracks'] as $t)
                                                <div class="bg-black/40 border border-white/5 rounded-xl p-4">
                                                    <div class="text-[10px] text-gray-500 font-title font-bold tracking-wider mb-2">{{ strtoupper($t) }}</div>
                                                    <div class="flex justify-between items-baseline mb-1">
                                                        <span class="font-title font-bold text-lg text-white">45</span>
                                                        <span class="text-[10px] text-gray-600">/ 100</span>
                                                    </div>
                                                    <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-gradient-to-r from-neon-blue to-neon-purple h-full rounded-full" style="width: 45%;"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="border-t border-white/5 pt-6 mt-8 flex items-center justify-between text-xs text-gray-500 font-title font-bold tracking-wider">
                                <span>STATUS: LOCKED</span>
                                <span class="text-neon-blue">REQUIRES LEVEL 10 TO AWAKEN</span>
                            </div>
                        </div>

                        <!-- Skill Nodes Grid (Right) -->
                        <div class="md:col-span-4 grid grid-cols-2 gap-4">
                            @foreach ($skills as $name => $details)
                                <button
                                    @click="activeSkill = '{{ $name }}'"
                                    :class="activeSkill === '{{ $name }}' ? 'bg-gradient-to-br from-neon-purple/20 to-neon-blue/20 border-neon-blue text-white shadow-neon-purple' : 'bg-obsidian-card border-white/5 text-gray-400 hover:border-white/10'"
                                    class="h-36 flex flex-col items-center justify-center p-4 rounded-xl border text-center transition duration-300 group"
                                >
                                    <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center font-bold text-neon-blue mb-3 group-hover:scale-110 transition duration-300">
                                        {!! $details['icon'] !!}
                                    </div>
                                    <span class="font-title font-bold text-[10px] tracking-widest break-words w-full">
                                        {{ strtoupper(explode(' (', $name)[0]) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </section>

                <!-- REAL-TIME HIGHLIGHTS: DAILY QUESTS & LEADERBOARD -->
                <section id="leaderboard" class="border-t border-white/5 bg-black/40 py-24">
                    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-12 gap-12">
                        <!-- Left: Live Daily Quests card -->
                        <div class="lg:col-span-5 bg-obsidian-card border border-white/10 rounded-2xl p-8 shadow-2xl relative hud-scanlines">
                            <h3 class="font-title text-2xl font-black text-white tracking-wide mb-2 flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-neon-blue animate-ping"></span>
                                DAILY SYSTEM QUEST LOG
                            </h3>
                            <p class="text-xs text-gray-500 font-title tracking-wider mb-6">TODAY'S MANDATORY TARGETS // OR TRIGGER PENALTY BREAK</p>

                            <div class="space-y-4">
                                <div class="flex items-center gap-4 p-4 rounded-xl bg-black/40 border border-white/5">
                                    <input type="checkbox" checked disabled class="rounded border-white/20 bg-transparent text-neon-blue focus:ring-neon-blue/30 w-5 h-5">
                                    <div>
                                        <div class="font-bold text-white text-sm">Physical Conditioning</div>
                                        <div class="text-xs text-gray-400">100 push-ups, squats, sit-ups & 10km run</div>
                                    </div>
                                    <span class="ml-auto text-[10px] font-title text-green-400 font-bold bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded">+25 XP</span>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-xl bg-black/40 border border-white/5">
                                    <input type="checkbox" checked disabled class="rounded border-white/20 bg-transparent text-neon-blue focus:ring-neon-blue/30 w-5 h-5">
                                    <div>
                                        <div class="font-bold text-white text-sm">Shadow coding / Skill Study</div>
                                        <div class="text-xs text-gray-400">Execute 2 hours of focused project work</div>
                                    </div>
                                    <span class="ml-auto text-[10px] font-title text-green-400 font-bold bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded">+25 XP</span>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-xl bg-black/40 border border-white/5 opacity-55">
                                    <input type="checkbox" disabled class="rounded border-white/20 bg-transparent text-neon-blue focus:ring-neon-blue/30 w-5 h-5">
                                    <div>
                                        <div class="font-bold text-white text-sm">Stoic Reflection Check-in</div>
                                        <div class="text-xs text-gray-400">Submit weekly emotional evaluation</div>
                                    </div>
                                    <span class="ml-auto text-[10px] font-title text-neon-blue font-bold bg-neon-blue/10 border border-neon-blue/20 px-2 py-0.5 rounded">+25 XP</span>
                                </div>
                            </div>

                            <div class="mt-8 p-4 bg-neon-purple/5 border border-neon-purple/20 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-title text-neon-purple font-black tracking-wider">PENALTY ZONE ACTIVE</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">Fail check-in before 23:59 to break your streak.</div>
                                </div>
                                <span class="text-red-400 font-bold text-xs font-title uppercase tracking-wider px-2 py-0.5 border border-red-500/20 bg-red-500/10 rounded animate-pulse">ACTIVE</span>
                            </div>
                        </div>

                        <!-- Right: Leaderboard Highlights -->
                        <div class="lg:col-span-7 flex flex-col justify-between">
                            <div>
                                <h3 class="font-title text-3xl font-black text-white tracking-wide mb-2">
                                    GLOBAL HUNTER STANDINGS
                                </h3>
                                <p class="text-gray-400 mb-8 text-sm md:text-base">
                                    Ascend the ranks. Watch live updates of top level Hunters fighting for rank positions in the global arena.
                                </p>

                                <div class="bg-obsidian-card border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
                                    <div class="overflow-x-auto w-full">
                                        <table class="w-full text-left border-collapse min-w-[600px]">
                                            <thead>
                                                <tr class="border-b border-white/5 bg-black/40 font-title text-[10px] tracking-widest text-gray-500">
                                                    <th class="py-4 px-6">RANK</th>
                                                    <th class="py-4 px-6">HUNTER NAME</th>
                                                    <th class="py-4 px-6">LEVEL</th>
                                                    <th class="py-4 px-6">GUILD</th>
                                                    <th class="py-4 px-6 text-right">STREAK</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/5 text-sm font-light">
                                                <tr class="hover:bg-white/5 transition duration-300">
                                                    <td class="py-4 px-6 font-title font-black text-gold-rpg">#01</td>
                                                    <td class="py-4 px-6 font-bold text-white flex items-center gap-3">
                                                        <img src="{{ asset('images/sung_jin_woo.png') }}" class="w-9 h-9 rounded-full border border-neon-blue/40 object-cover shrink-0">
                                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5">
                                                            <span>Sung Jin-Woo</span>
                                                            <span class="text-[9px] text-neon-blue font-title font-black border border-neon-blue/20 bg-neon-blue/10 px-1.5 py-0.5 rounded uppercase tracking-wider w-max">Monarch</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6 font-title text-neon-purple font-black">Lv. 142</td>
                                                    <td class="py-4 px-6 text-gray-400">Ahjin</td>
                                                    <td class="py-4 px-6 text-right text-gold-rpg font-title font-bold">120 Days</td>
                                                </tr>
                                                <tr class="hover:bg-white/5 transition duration-300">
                                                    <td class="py-4 px-6 font-title font-black text-gray-400">#02</td>
                                                    <td class="py-4 px-6 font-bold text-white flex items-center gap-3">
                                                        <img src="{{ asset('images/cha_hae_in.png') }}" class="w-9 h-9 rounded-full border border-neon-purple/40 object-cover shrink-0">
                                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5">
                                                            <span>Cha Hae-In</span>
                                                            <span class="text-[9px] text-neon-purple font-title font-black border border-neon-purple/20 bg-neon-purple/10 px-1.5 py-0.5 rounded uppercase tracking-wider w-max">S-Rank</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6 font-title text-neon-purple font-black">Lv. 94</td>
                                                    <td class="py-4 px-6 text-gray-400">Hunters</td>
                                                    <td class="py-4 px-6 text-right text-neon-blue font-title font-bold">81 Days</td>
                                                </tr>
                                                <tr class="hover:bg-white/5 transition duration-300">
                                                    <td class="py-4 px-6 font-title font-black text-amber-700">#03</td>
                                                    <td class="py-4 px-6 font-bold text-white flex items-center gap-3">
                                                        <img src="{{ asset('images/thomas_andre.png') }}" class="w-9 h-9 rounded-full border border-gold-rpg/40 object-cover shrink-0">
                                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5">
                                                            <span>Thomas Andre</span>
                                                            <span class="text-[9px] text-gold-rpg font-title font-black border border-gold-rpg/20 bg-gold-rpg/10 px-1.5 py-0.5 rounded uppercase tracking-wider w-max">National</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6 font-title text-neon-purple font-black">Lv. 91</td>
                                                    <td class="py-4 px-6 text-gray-400">Scavenger</td>
                                                    <td class="py-4 px-6 text-right text-neon-blue font-title font-bold">54 Days</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 text-center lg:text-left">
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 font-title text-xs tracking-widest text-neon-blue hover:underline font-black shadow-neon-blue">
                                    CLAIM YOUR POSITION ON THE GLOBAL LEADERBOARD →
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- AI SHADOW GUIDE PANEL -->
                <section id="shadow-guide" class="py-24 max-w-7xl mx-auto px-6">
                    <div class="bg-gradient-to-r from-obsidian-card to-[#090b12] border border-neon-purple/30 rounded-3xl p-8 md:p-12 shadow-2xl relative overflow-hidden neon-border">
                        <!-- Neon background grid overlay -->
                        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(138,43,226,0.04)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>

                        <div class="grid md:grid-cols-12 gap-8 items-center relative z-10">
                            <div class="md:col-span-7 space-y-6">
                                <span class="font-title text-[9px] tracking-widest font-black text-neon-purple bg-neon-purple/10 px-3 py-1 rounded-full border border-neon-purple/20">
                                    AI COACHING COGNITIVE SUBMODULE
                                </span>
                                <h3 class="font-title text-3xl md:text-5xl font-black text-white tracking-wide">
                                    THE AI SHADOW GUIDE
                                </h3>
                                <p class="text-gray-300 text-base md:text-lg leading-relaxed font-light">
                                    Receive real-time psychological reports and coaching directives. The system logs your checked-in habit domains, analyzes fatigue levels, and adapts the quest line to push you past plateaus.
                                </p>

                                <div class="flex items-center gap-6">
                                    <div class="flex -space-x-4">
                                        <div class="w-10 h-10 rounded-full border-2 border-obsidian bg-gradient-to-br from-neon-blue to-black flex items-center justify-center font-bold text-[10px] text-white">S</div>
                                        <div class="w-10 h-10 rounded-full border-2 border-obsidian bg-gradient-to-br from-neon-purple to-black flex items-center justify-center font-bold text-[10px] text-white">E</div>
                                        <div class="w-10 h-10 rounded-full border-2 border-obsidian bg-gradient-to-br from-gold-rpg to-black flex items-center justify-center font-bold text-[10px] text-white">M</div>
                                    </div>
                                    <span class="text-[10px] text-gray-500 font-title font-bold tracking-widest uppercase">
                                        JOIN 42,912 ACTIVE HUNTERS
                                    </span>
                                </div>
                            </div>

                            <!-- Interactive typing terminal simulator -->
                            <div class="md:col-span-5 bg-black/80 rounded-2xl p-6 border border-white/10 font-mono text-sm shadow-inner relative">
                                <div class="flex items-center gap-2 mb-4 border-b border-white/5 pb-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                    <span class="text-[10px] text-gray-500 font-title ml-2">SHADOW_GUIDE_PROMPT_v1.0</span>
                                </div>

                                <div class="space-y-4 text-xs md:text-sm">
                                    <div class="text-neon-purple font-semibold">&gt; Initializing neural connection...</div>
                                    <div class="text-neon-blue">&gt; Target acquired: Hunter Guest.</div>
                                    <div class="text-white/90 animate-pulse font-light">
                                        "The system offers you a simple choice. Accept the contract and undergo daily trial, or stay locked in the ordinary world. Level up. Your destiny is waiting."
                                    </div>
                                    <div class="text-gray-500 text-[10px]">&gt; Status: Awaiting check-in initialization.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/5 bg-black/85 py-12 text-center text-[10px] tracking-widest text-gray-600 font-title font-bold">
                <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        © {{ date('Y') }} ARISE INC. WAKE UP TO THE EVOLUTION. ALL RIGHTS RESERVED.
                    </div>
                    <div class="flex gap-6 text-gray-500 text-[9px] uppercase font-bold">
                        <a href="#" class="hover:text-neon-blue transition">Terms of Service</a>
                        <a href="#" class="hover:text-neon-blue transition">Privacy Protocol</a>
                        <a href="#" class="hover:text-neon-blue transition">System Core API</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
