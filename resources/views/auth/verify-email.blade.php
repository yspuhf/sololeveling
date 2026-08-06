<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-title text-xl font-bold text-white tracking-wider mb-2">SYSTEM VERIFICATION</h2>
        <div class="w-16 h-1 bg-gradient-to-r from-neon-blue to-neon-purple mx-auto rounded-full"></div>
    </div>

    <div class="mb-6 text-sm text-gray-400 text-center leading-relaxed">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 font-medium text-xs text-neon-blue border border-neon-blue/30 bg-neon-blue/5 px-4 py-3 rounded-lg shadow-neon-blue/10">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 font-medium text-xs text-red-400 border border-red-500/30 bg-red-500/5 px-4 py-3 rounded-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf

            <button type="submit" class="w-full sm:w-auto font-title text-xs tracking-widest font-black uppercase px-6 py-3 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-slate-950 hover:shadow-neon-blue transition-all duration-300 cursor-pointer">
                {{ __('Resend Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
            @csrf

            <button type="submit" class="underline text-xs text-gray-400 hover:text-white transition-colors duration-200 cursor-pointer">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
