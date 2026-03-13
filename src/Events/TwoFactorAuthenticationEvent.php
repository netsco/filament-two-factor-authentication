<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Events;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Events\Dispatchable;

abstract class TwoFactorAuthenticationEvent
{
    use Dispatchable;

    /**
     * The user instance.
     *
     * @var FilamentUser
     */
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(FilamentUser $user)
    {
        $this->user = $user;
    }
}
