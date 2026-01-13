<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Events;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Events\Dispatchable;

class RecoveryCodesGenerated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The user instance.
         */
        public FilamentUser $user
    )
    {
    }
}
