<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Events;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The authenticated user.
         */
        public FilamentUser $user,
        /**
         * The recovery code.
         */
        public string $code
    ) {}
}
