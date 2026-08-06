<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('EMAIL ADDRESS')" class="text-xs text-gray-500 font-title font-bold tracking-widest mb-1.5 block" />
            <x-text-input wire:model="form.email" id="email" class="block w-full bg-black/40 border border-white/10 text-white focus:border-neon-purple focus:ring-1 focus:ring-neon-purple/20 rounded-lg p-3 outline-none text-sm font-semibold" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2 text-red-400 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('PASSWORD CODE')" class="text-xs text-gray-500 font-title font-bold tracking-widest mb-1.5 block" />

            <x-text-input wire:model="form.password" id="password" class="block w-full bg-black/40 border border-white/10 text-white focus:border-neon-purple focus:ring-1 focus:ring-neon-purple/20 rounded-lg p-3 outline-none text-sm font-semibold"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-red-400 text-xs" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-2">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-white/20 bg-transparent text-neon-purple focus:ring-neon-purple/30 w-4.5 h-4.5" name="remember">
                <span class="ms-2 text-xs font-title font-bold text-gray-500 tracking-wider hover:text-gray-300 transition duration-300">{{ __('REMEMBER ME') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-xs text-gray-500 font-title font-bold tracking-wider hover:text-white transition duration-300" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('FORGOT PASSWORD?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-white/5">
            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-neon-blue via-neon-purple to-gold-rpg text-obsidian-dark font-title font-black text-xs tracking-widest rounded-lg shadow-neon-blue hover:scale-102 hover:opacity-95 transition duration-300">
                {{ __('ACCESS COGNITIVE PORTAL') }}
            </button>
        </div>
    </form>
</div>
