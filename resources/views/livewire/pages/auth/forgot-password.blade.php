<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state(['email' => '']);

rules(['email' => ['required', 'string', 'email']]);

$sendPasswordResetLink = function () {
    $this->validate();

    // We will send the password reset link to this user. Once we have attempted
    // to send the link, we will examine the response then see the message we
    // need to show to the user. Finally, we'll send out a proper response.
    $status = Password::sendResetLink(
        $this->only('email')
    );

    if ($status != Password::RESET_LINK_SENT) {
        $this->addError('email', __($status));

        return;
    }

    $this->reset('email');

    Session::flash('status', __($status));
};

?>

<div>
    <div class="mb-6 text-xs text-slate-400 font-sans tracking-wide leading-relaxed">
        {{ __('Forgot your password? No problem. Provide your email credentials and the system will transmit a password reset link to authorize profile decryption.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6 pt-4 border-t border-white/5">
            <a class="underline text-xs text-slate-400 font-title font-bold tracking-wider hover:text-white transition duration-300" href="{{ route('login') }}" wire:navigate>
                {{ __('BACK TO LOGIN') }}
            </a>

            <x-primary-button>
                {{ __('SEND RESET LINK') }}
            </x-primary-button>
        </div>
    </form>
</div>
