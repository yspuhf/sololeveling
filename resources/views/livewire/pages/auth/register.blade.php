<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => ''
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$register = function () {
    if (!\App\Services\FeatureEntitlementService::isGloballyEnabled('registration')) {
        $this->addError('email', 'New user registration is currently disabled.');
        return;
    }

    $validated = $this->validate();

    $validated['password'] = Hash::make($validated['password']);

    event(new Registered($user = User::create($validated)));

    Auth::login($user);

    $this->redirect(route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    @if (!\App\Services\FeatureEntitlementService::isGloballyEnabled('registration'))
        <div class="text-center space-y-5 py-6">
            <div class="w-16 h-16 bg-red-500/10 border border-red-500/20 rounded-full flex items-center justify-center mx-auto text-red-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="font-title text-base font-black text-red-500 tracking-widest uppercase">REGISTRATION GATEWAY: LOCKED</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">The gateway to awaken new hunter profiles is currently closed by order of the Association.</p>
            </div>
            <div class="pt-4 border-t border-white/5">
                <a href="{{ route('login') }}" class="px-6 py-2.5 bg-gradient-to-r from-neon-blue to-neon-purple text-obsidian-dark font-title font-black text-xs tracking-wider rounded-lg shadow-neon-blue inline-block hover:opacity-90 transition duration-300">
                    RETURN TO LOGIN
                </a>
            </div>
        </div>
    @else
        <form wire:submit="register">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-white/5">
                <a class="underline text-xs text-slate-400 font-title font-bold tracking-wider hover:text-white transition duration-300" href="{{ route('login') }}" wire:navigate>
                    {{ __('ALREADY REGISTERED?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('CREATE HUNTER PROFILE') }}
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
