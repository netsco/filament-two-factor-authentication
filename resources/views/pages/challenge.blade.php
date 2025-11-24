<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{__('filament-two-factor-authentication::pages.subheading').' ' }}
        {{ $this->recoveryAction }}
    </x-slot>

    <x-filament-panels::form id="form" wire:submit="authenticate">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            {{ __('filament-two-factor-authentication::pages.challenge.confirm') }}
        </p>

        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <x-filament-two-factor-authentication::logout />

</x-filament-panels::page.simple>
