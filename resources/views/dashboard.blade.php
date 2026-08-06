<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight font-title tracking-wider">
            {{ __('SYSTEM CONTROL PANELS') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <livewire:hunter-dashboard />
    </div>
</x-app-layout>

