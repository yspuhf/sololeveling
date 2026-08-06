<x-guest-layout>
    <div class="mb-6 text-center">
        <!-- SVG Warning/Failed Icon -->
        <div class="w-16 h-16 bg-red-500/10 border border-red-500/30 rounded-full flex items-center justify-center mx-auto mb-4 shadow-red-500/10">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 class="font-title text-xl font-bold text-white tracking-wider mb-2">VERIFICATION FAILED</h2>
        <div class="w-24 h-0.5 bg-gradient-to-r from-red-500 to-neon-purple mx-auto rounded-full"></div>
    </div>

    <div class="mb-8 text-sm text-gray-400 text-center leading-relaxed">
        @if (isset($error))
            {{ $error }}
        @else
            Your verification link has either been tampered with or has broken past its 24-hour expiration threshold.
        @endif
    </div>

    <div class="flex flex-col gap-4 text-center">
        <a href="{{ route('verification.notice') }}" class="inline-block font-title text-xs tracking-widest font-black uppercase px-6 py-3 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-slate-950 hover:shadow-neon-blue transition-all duration-300">
            Re-request Verification Link
        </a>
        
        <a href="/" class="text-xs text-gray-400 hover:text-white transition-colors duration-200 underline cursor-pointer">
            Back to Home
        </a>
    </div>
</x-guest-layout>
