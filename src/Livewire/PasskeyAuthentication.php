<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Livewire;

use DeviceDetector\DeviceDetector;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Spatie\LaravelPasskeys\Livewire\PasskeysComponent;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

class PasskeyAuthentication extends PasskeysComponent implements HasActions, HasForms, HasTable
{
    use Defaults;
    use InteractsWithTable;

    public bool $aside = true;

    public function getBrowserAndDevice(?string $userAgent = null): string
    {
        // If no user agent is provided, use the current one from the request.
        // The `?? ''` ensures it's a string even if the server variable is not set.
        if ($userAgent === null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        // 1. Get the browser name
        $browserName = $dd->getClient()['name'] ?? 'Unknown Browser';

        // 2. Determine the best device/platform name
        $deviceName = 'Unknown Device'; // Start with a default

        if ($dd->isDesktop()) {
            // For desktops, the OS name is most descriptive (e.g., "Windows", "Mac")
            $deviceName = $dd->getOs()['name'] ?? 'Desktop';
        } elseif ($dd->getBrandName() === 'Apple' && $dd->isMobile()) {
            // Specifically identify iPhones and iPads
            $deviceType = $dd->getDeviceName();
            if ($deviceType === 'smartphone') {
                $deviceName = 'iPhone';
            } elseif ($deviceType === 'tablet') {
                $deviceName = 'iPad';
            } else {
                $deviceName = 'Apple Device'; // Fallback for iPod, etc.
            }
        } elseif (($dd->getOs()['name'] ?? null) === 'Android') {
            // For Android, the OS name itself is clear and standard
            $deviceName = 'Android';
        } elseif ($dd->isMobile()) {
            // For other mobile devices, use the OS name as a fallback
            $deviceName = $dd->getOs()['name'] ?? 'Mobile Device';
        }

        // 3. Combine them and return the final string
        return "{$browserName} on {$deviceName}";
    }

    public function render(): View
    {
        return view('filament-two-factor-authentication::livewire.passkey-authentication');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getUser()->passkeys()->latest())
            ->headerActions([
                Action::make('addPasskey')
                    ->label(__('filament-two-factor-authentication::components.passkey.add'))
                    ->modalDescription(__('filament-two-factor-authentication::components.passkey.description'))
                    ->modalWidth(MaxWidth::Medium)
                    ->form([
                        TextInput::make('name')
                            ->default(fn () => $this->getBrowserAndDevice())
                            ->label(__('filament-two-factor-authentication::components.passkey.name'))
                            ->required()
                            ->autocomplete(false),
                    ])
                    ->modalSubmitActionLabel(__('filament-two-factor-authentication::components.passkey.submit'))
                    ->action(function ($data, Action $action) {
                        $this->name = $data['name'];

                        // Dispatch to self so $wire.on() can catch it
                        $this->dispatch('passkeyPropertiesValidated', [
                            'passkeyOptions' => json_decode($this->generatePasskeyOptions()),
                        ])->self();

                        // Halt to keep the modal open - it will be closed after storePasskey succeeds
                        $action->halt();
                    }),
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label(__('Name'))
                        ->description(fn ($record) => $record->last_used_at
                            ? $record->last_used_at->diffForHumans()
                            : __('Never used')),
                ]),
            ])
            ->actions([
                DeleteAction::make()
                    ->form(function () {
                        if (! TwoFactorAuthenticationPlugin::get()->twoFactorSetupRequiresPassword()) {
                            return null;
                        }

                        return [
                            TextInput::make('currentPassword')
                                ->label(__('filament-two-factor-authentication::components.2fa.current_password'))
                                ->password()
                                ->revealable(filament()->arePasswordsRevealable())
                                ->required()
                                ->autocomplete('current-password')
                                ->rules([
                                    fn () => function (string $attribute, $value, $fail) {
                                        if (! \Hash::check($value, $this->getUser()->password)) {
                                            $fail(
                                                __('filament-two-factor-authentication::components.2fa.wrong_password')
                                            );
                                        }
                                    },
                                ]),
                        ];
                    }),
            ])
            ->paginated(false);
    }

    public function storePasskey(string $passkey): void
    {
        parent::storePasskey($passkey);

        Notification::make()
            ->title(__('filament-two-factor-authentication::components.passkey.added'))
            ->success()
            ->send();

        // Unmount the action to close the modal cleanly
        $this->unmountTableAction();
    }

    #[On('passkeyRegistrationFailed')]
    public function handlePasskeyRegistrationFailed(): void
    {
        // Unmount the action to close modal on failure too
        $this->unmountTableAction();
    }
}
