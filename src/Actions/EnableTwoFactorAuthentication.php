<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Actions;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Stephenjude\FilamentTwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider;
use Stephenjude\FilamentTwoFactorAuthentication\Events\TwoFactorAuthenticationEnabled;

class EnableTwoFactorAuthentication
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        /**
         * The two factor authentication provider.
         */
        protected TwoFactorAuthenticationProvider $provider
    )
    {
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param  FilamentUser&Model  $user
     */
    public function __invoke(FilamentUser $user, bool $force = false): void
    {
        if (empty($user->two_factor_secret) || $force) {
            $user->forceFill([
                'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn() => RecoveryCode::generate())->all())),
            ])->save();

            /** @phpstan-ignore method.notFound */
            $user->setTwoFactorChallengePassed();

            TwoFactorAuthenticationEnabled::dispatch($user);
        }
    }
}
