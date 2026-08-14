<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ARISE // ASSOCIATION MANAGEMENT</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
            .font-title {
                font-family: 'Orbitron', sans-serif;
            }
            /* Custom purple neon scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: #090b12;
            }
            ::-webkit-scrollbar-thumb {
                background: #8b5cf6;
                border-radius: 3px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #a78bfa;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-obsidian-dark text-gray-300">
        <div class="min-h-screen flex flex-col md:flex-row">
            
            <!-- Sidebar -->
            <aside class="w-full md:w-64 bg-obsidian-card border-r border-white/5 flex flex-col justify-between z-20 shrink-0">
                <div>
                    <!-- Logo / Association title -->
                    <div class="p-6 border-b border-white/5 flex items-center justify-between">
                        <div class="space-y-1">
                            <h1 class="font-title text-base font-black text-white tracking-widest flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-neon-purple animate-pulse"></span>
                                ASSOCIATION
                            </h1>
                            <p class="text-[9px] text-neon-purple font-mono font-bold tracking-wider">MANAGEMENT_CONSOLE_v1.0</p>
                        </div>
                    </div>

                    <!-- Sidebar navigation menu -->
                    <nav class="p-4 space-y-1.5">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            📊 DASHBOARD
                        </a>
                        <a href="{{ route('admin.users') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.users*') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            👥 HUNTERS LIST
                        </a>
                        <a href="{{ route('admin.payments') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.payments') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            💳 TRANSACTIONS
                        </a>
                        <a href="{{ route('admin.subscriptions') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.subscriptions') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            📜 SUBSCRIPTIONS
                        </a>
                        <a href="{{ route('admin.contracts') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.contracts') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            ⚔️ CONTRACT ANALYTICS
                        </a>
                        <a href="{{ route('admin.features') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.features') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            ⚙️ FEATURE CONTROL
                        </a>
                        <a href="{{ route('admin.plans') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.plans') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            💎 MEMBERSHIP PLANS
                        </a>
                        <a href="{{ route('admin.audit-logs') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-title text-xs font-bold tracking-wider transition-all duration-300 {{ request()->routeIs('admin.audit-logs') ? 'bg-neon-purple/10 border border-neon-purple/20 text-white shadow-neon-purple/5' : 'text-slate-400 hover:text-white border border-transparent' }}">
                            📜 AUDIT LOGS
                        </a>
                    </nav>
                </div>

                <!-- Footer / Logout -->
                <div class="p-4 border-t border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="pl-2">
                            <div class="text-xs font-semibold text-white truncate max-w-[140px]">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-neon-purple font-mono font-bold tracking-wider uppercase">SUPER ADMIN</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center py-2.5 border border-white/10 hover:border-red-500/20 hover:bg-red-500/5 text-gray-400 hover:text-red-400 font-title font-bold text-xs tracking-widest rounded-xl transition duration-300">
                            LOGOUT
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Nav Bar -->
                <header class="h-16 bg-obsidian-card/85 backdrop-blur border-b border-white/5 flex items-center justify-between px-6 z-10 sticky top-0">
                    <div class="flex items-center gap-4">
                        <h2 class="text-white font-title text-sm font-black tracking-widest uppercase">
                            @yield('page_title', 'ARISE CONSOLE')
                        </h2>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-mono font-bold text-slate-400">
                        <span>SYSTEM_STATUS: <span class="text-green-400">ONLINE</span></span>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 p-6 md:p-8 overflow-y-auto max-h-[calc(100vh-4rem)]">
                    {{ $slot }}
                </main>
            </div>

        </div>
    </body>
</html>
