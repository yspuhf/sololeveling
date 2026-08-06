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
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

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
            }
            .font-title {
                font-family: 'Orbitron', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-obsidian-dark text-gray-300">
        <div class="min-h-screen bg-obsidian-dark">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-obsidian-card/85 backdrop-blur border-b border-white/5 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="relative z-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

