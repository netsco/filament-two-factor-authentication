<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Forms\Components;

use Filament\Forms\Components\TextInput;

class DigitInputGroup
{
    public static function make(?callable $validation = null): TextInput
    {
        $input = TextInput::make('code')
            ->label(__('filament-two-factor-authentication::components.2fa.code'))
            ->hiddenLabel()
            ->maxLength(6)
            ->minLength(6)
            ->inputMode('numeric')
            ->extraInputAttributes([
                'pattern' => '[0-9]{6}',
                'autocomplete' => 'one-time-code',
                'class' => 'fi-digit-input !tracking-[2rem] !text-center !text-2xl !font-semibold',
            ])
            ->extraAttributes([
                'class' => 'fi-digit-input-wrapper',
            ])
            ->required();

        if ($validation) {
            $input->rules([$validation]);
        }

        return $input;
    }
}
