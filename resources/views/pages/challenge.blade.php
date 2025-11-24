<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{__('filament-two-factor-authentication::pages.subheading').' ' }}
        {{ $this->recoveryAction }}
    </x-slot>

    <x-filament-panels::form id="form" wire:submit="authenticate">
        <div class="space-y-6">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('filament-two-factor-authentication::pages.challenge.confirm') }}
                </p>

                <x-filament-two-factor-authentication::code-input
                    wire:model="data.code"
                    :error="$getErrorsForPath('data.code')"
                />

                @error('data.code')
                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-2">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{ $this->form }}
        </div>

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <x-filament-two-factor-authentication::logout />

</x-filament-panels::page.simple>
