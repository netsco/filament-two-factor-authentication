<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Actions;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Stephenjude\FilamentTwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider;
use Stephenjude\FilamentTwoFactorAuthentication\Events\TwoFactorAuthenticationConfirmed;

class ConfirmTwoFactorAuthentication
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        /**
         * The two factor authentication provider.
         */
        protected TwoFactorAuthenticationProvider $provider
    ) {}

    /**
     * Confirm the two factor authentication configuration for the user.
     *
     * @param  FilamentUser&Model  $user
     */
    public function __invoke(FilamentUser $user, string $code): void
    {
        if (empty($user->two_factor_secret) ||
            ($code === '' || $code === '0') ||
            ! $this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'data.code' => __('filament-two-factor-authentication::actions.confirm_two_factor_authentication.wrong_code'),
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
