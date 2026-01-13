<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Actions;

use Illuminate\Support\Str;

class RecoveryCode
{
    /**
     * Generate a new recovery code.
     */
    public static function generate(): string
    {
        return Str::random(10) . '-' . Str::random(10);
    }
}
