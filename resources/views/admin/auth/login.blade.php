<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center space-y-1">
            <h2 class="font-title text-lg font-black text-white tracking-widest uppercase">ADMIN AWAKENING</h2>
            <p class="text-[10px] text-neon-purple font-mono font-bold tracking-widest">ASSOCIATION SYSTEM ACCESS CONSOLE</p>
        </div>

        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-lg text-xs font-semibold text-center">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('EMAIL ADDRESS')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('DECRYPTION KEY')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-white/20 bg-black/40 text-neon-purple focus:ring-0 focus:ring-offset-0" name="remember">
                    <span class="ms-2 text-xs text-slate-400 font-title font-bold tracking-wider">{{ __('KEEP SESSION ACTIVE') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-6 pt-4 border-t border-white/5">
                <a class="underline text-xs text-slate-400 font-title font-bold tracking-wider hover:text-white transition duration-300" href="{{ route('login') }}">
                    {{ __('USER ACCESS') }}
                </a>

                <x-primary-button class="ms-4 bg-gradient-to-r from-neon-purple to-pink-500 border-none shadow-neon-purple/20">
                    {{ __('AWAKEN ACCESS') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
