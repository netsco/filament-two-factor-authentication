<?php

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('does not use env() helper')
    ->expect('env')
    ->not->toBeUsed();

it('actions are classes')
    ->expect('Stephenjude\FilamentTwoFactorAuthentication\Actions')
    ->toBeClasses();

it('events are classes')
    ->expect('Stephenjude\FilamentTwoFactorAuthentication\Events')
    ->toBeClasses();

it('middleware has handle method')
    ->expect('Stephenjude\FilamentTwoFactorAuthentication\Middleware')
    ->toBeClasses()
    ->toHaveMethod('handle');

it('pages extend base simple page')
    ->expect('Stephenjude\FilamentTwoFactorAuthentication\Pages')
    ->classes()
    ->not->toBeFinal()
    ->toExtend('Stephenjude\FilamentTwoFactorAuthentication\Pages\BaseSimplePage');

it('livewire components extend component')
    ->expect('Stephenjude\FilamentTwoFactorAuthentication\Livewire')
    ->classes()
    ->toExtend('Livewire\Component');
