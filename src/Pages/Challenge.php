<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Stephenjude\FilamentTwoFactorAuthentication\Events\TwoFactorAuthenticationChallenged;
use Stephenjude\FilamentTwoFactorAuthentication\Events\TwoFactorAuthenticationFailed;
use Stephenjude\FilamentTwoFactorAuthentication\Events\ValidTwoFactorAuthenticationCodeProvided;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationProvider;

class Challenge extends BaseSimplePage
{
    protected static string $view = 'filament-two-factor-authentication::pages.challenge';

    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __('filament-two-factor-authentication::section.header');
    }

    public function mount(): void
    {
        if (! Filament::auth()->check()) {
            redirect()->to(filament()->getCurrentPanel()?->getLoginUrl());

            return;
        }

        $user = Filament::auth()->user();

        $this->form->fill();

        TwoFactorAuthenticationChallenged::dispatch($user);
    }

    public function recoveryAction(): Action
    {
        return Action::make('recovery')
            ->link()
            ->label(__('filament-two-factor-authentication::pages.challenge.action_label'))
            ->url(
                filament()->getCurrentPanel()->route(
                    'two-factor.recovery'
                )
            );
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);

            $this->form->getState();

            $user = Filament::auth()->user();

            $user->setTwoFactorChallengePassed();

            event(new ValidTwoFactorAuthenticationCodeProvided($user));

            return app(LoginResponse::class);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        $makeDigitInput = function (int $index) {
            return TextInput::make("digit{$index}")
                ->hiddenLabel()
                ->maxLength(1)
                ->inputMode('numeric')
                ->extraInputAttributes([
                    'class' => 'text-center !text-2xl !font-semibold aspect-square',
                    'pattern' => '[0-9]',
                    'x-ref' => "digit{$index}",
                    'x-on:input' => "handleInput({$index}, \$event)",
                    'x-on:keydown' => "handleKeydown({$index}, \$event)",
                    'x-on:paste' => $index === 0 ? 'handlePaste($event)' : '',
                ])
                ->required();
        };

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Group::make([
                            $makeDigitInput(0),
                            $makeDigitInput(1),
                            $makeDigitInput(2),
                            $makeDigitInput(3),
                            $makeDigitInput(4),
                            $makeDigitInput(5)
                                ->rules([
                                    fn () => function (string $attribute, $value, $fail) {
                                        // Combine all digits
                                        $code = ($this->data['digit0'] ?? '') .
                                                ($this->data['digit1'] ?? '') .
                                                ($this->data['digit2'] ?? '') .
                                                ($this->data['digit3'] ?? '') .
                                                ($this->data['digit4'] ?? '') .
                                                ($this->data['digit5'] ?? '');

                                        $user = Filament::auth()->user();
                                        if (is_null($user)) {
                                            $fail(__('filament-two-factor-authentication::pages.challenge.error'));

                                            redirect()->to(filament()->getCurrentPanel()->getLoginUrl());

                                            return;
                                        }

                                        $isValidCode = app(TwoFactorAuthenticationProvider::class)->verify(
                                            secret: decrypt($user->two_factor_secret),
                                            code: $code
                                        );

                                        if (! $isValidCode) {
                                            Notification::make()
                                                ->title(__('filament-two-factor-authentication::pages.challenge.notification.title'))
                                                ->body(__('filament-two-factor-authentication::pages.challenge.notification.body'))
                                                ->danger()
                                                ->send();

                                            // Clear all digit fields
                                            $this->data['digit0'] = '';
                                            $this->data['digit1'] = '';
                                            $this->data['digit2'] = '';
                                            $this->data['digit3'] = '';
                                            $this->data['digit4'] = '';
                                            $this->data['digit5'] = '';

                                            $fail(__('filament-two-factor-authentication::pages.challenge.error'));

                                            event(new TwoFactorAuthenticationFailed($user));
                                        }
                                    },
                                ]),
                        ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->extraAttributes([
                                'x-data' => '{
                                    init() {
                                        this.$nextTick(() => this.$refs.digit0?.focus());
                                    },
                                    handleInput(index, event) {
                                        const input = event.target;
                                        const value = input.value;

                                        if (value && !/^\d$/.test(value)) {
                                            input.value = "";
                                            return;
                                        }

                                        if (value && index < 5) {
                                            this.$refs["digit" + (index + 1)]?.focus();
                                        }
                                    },
                                    handleKeydown(index, event) {
                                        const input = event.target;

                                        if (event.key === "Backspace") {
                                            if (!input.value && index > 0) {
                                                event.preventDefault();
                                                this.$refs["digit" + (index - 1)]?.focus();
                                            }
                                        } else if (event.key === "ArrowLeft" && index > 0) {
                                            event.preventDefault();
                                            this.$refs["digit" + (index - 1)]?.focus();
                                        } else if (event.key === "ArrowRight" && index < 5) {
                                            event.preventDefault();
                                            this.$refs["digit" + (index + 1)]?.focus();
                                        } else if (event.key.length === 1 && !/^\d$/.test(event.key)) {
                                            event.preventDefault();
                                        }
                                    },
                                    handlePaste(event) {
                                        event.preventDefault();
                                        const pastedData = event.clipboardData.getData("text");
                                        const digits = pastedData.replace(/\D/g, "").slice(0, 6);

                                        if (digits.length === 6) {
                                            for (let i = 0; i < 6; i++) {
                                                const input = this.$refs["digit" + i];
                                                if (input) {
                                                    input.value = digits[i];
                                                    input.dispatchEvent(new Event("input", { bubbles: true }));
                                                }
                                            }
                                            this.$refs.digit5?.focus();
                                        }
                                    }
                                }',
                            ]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    public function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getErrorsForPath(string $path): bool
    {
        return $this->getErrorBag()->has($path);
    }
}
