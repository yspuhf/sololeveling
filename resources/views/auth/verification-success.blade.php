<x-guest-layout>
    <div class="mb-6 text-center">
        <!-- SVG Success Icon -->
        <div class="w-16 h-16 bg-neon-blue/10 border border-neon-blue/30 rounded-full flex items-center justify-center mx-auto mb-4 shadow-neon-blue/10">
            <svg class="w-8 h-8 text-neon-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="font-title text-xl font-bold text-white tracking-wider mb-2">EMAIL VERIFIED SUCCESSFULLY</h2>
        <div class="w-24 h-0.5 bg-gradient-to-r from-neon-blue to-neon-purple mx-auto rounded-full"></div>
    </div>

    <div class="mb-8 text-sm text-gray-400 text-center leading-relaxed">
        Your account has been activated successfully. You may now log in.
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="inline-block font-title text-xs tracking-widest font-black uppercase px-8 py-3 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-slate-950 hover:shadow-neon-blue transition-all duration-300">
            Proceed to Login
        </a>
    </div>
</x-guest-layout>
