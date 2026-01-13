<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Livewire;

use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

/**
 * @deprecated Use the Defaults trait directly in your component instead
 */
abstract class BaseLivewireComponent extends Component implements HasActions, HasForms
{
    use Defaults;
}
