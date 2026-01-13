<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Actions;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Stephenjude\FilamentTwoFactorAuthentication\Events\RecoveryCodesGenerated;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  FilamentUser&Model  $user
     */
    public function __invoke(FilamentUser $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(
                json_encode(
                    Collection::times(8, fn() => RecoveryCode::generate())->all()
                )
            ),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
