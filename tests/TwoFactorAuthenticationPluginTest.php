<?php

use Stephenjude\FilamentTwoFactorAuthentication\Livewire\PasskeyAuthentication;
use Stephenjude\FilamentTwoFactorAuthentication\Livewire\TwoFactorAuthentication;
use Stephenjude\FilamentTwoFactorAuthentication\Pages\Challenge;
use Stephenjude\FilamentTwoFactorAuthentication\Pages\Recovery;
use Stephenjude\FilamentTwoFactorAuthentication\Pages\Setup;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->plugin = TwoFactorAuthenticationPlugin::get();
});

it('enables two factor authentication', function () {
    $this->plugin->enableTwoFactorAuthentication();

    expect($this->plugin->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

it('enables passkey authentication with default config', function () {
    $this->plugin->enablePasskeyAuthentication();

    expect($this->plugin->hasEnabledPasskeyAuthentication())->toBeTrue();
    expect($this->plugin->showsPasskeyLoginButton())->toBeTrue();
    expect($this->plugin->hasEnabledPasskeyAutofill())->toBeFalse();
});

it('configures passkey login button and autofill options', function () {
    $this->plugin->enablePasskeyAuthentication(
        condition: true,
        showLoginButton: false,
        enableAutofill: true
    );

    expect($this->plugin->hasEnabledPasskeyAuthentication())->toBeTrue();
    expect($this->plugin->showsPasskeyLoginButton())->toBeFalse();
    expect($this->plugin->hasEnabledPasskeyAutofill())->toBeTrue();
});

it('adds 2FA to user menu item', function () {
    $this->plugin->addTwoFactorMenuItem();

    expect($this->plugin->hasTwoFactorMenuItem())->toBeTrue();
});

it('sets custom challenge middleware on plugin', function () {
    $middleware = 'CustomMiddleware';
    $this->plugin->enableTwoFactorAuthentication(true, $middleware);

    expect($this->plugin->getTwoFactorChallengeMiddleware())->toBe($middleware);
});

it('forces setup and toggles password requirement on plugin', function () {
    $this->plugin->forceTwoFactorSetup(true, false);

    expect($this->plugin->hasForcedTwoFactorSetup())->toBeTrue();
    expect($this->plugin->twoFactorSetupRequiresPassword())->toBeFalse();
});

it('can render setup page', function () {
    livewire(Setup::class)->assertSuccessful();
});

it('can render challenge page', function () {
    livewire(Challenge::class)
        ->assertSuccessful();
});

it('can render recovery page', function () {
    livewire(Recovery::class)
        ->assertSuccessful();
});

it('can render two factor component', function () {
    livewire(TwoFactorAuthentication::class)
        ->assertSuccessful();
});

it('can render passkey component', function () {
    livewire(PasskeyAuthentication::class)
        ->assertSuccessful()
        ->assertTableActionExists('delete')
        ->assertTableHeaderActionsExistInOrder(['addPasskey']);
});

it('dispatches passkeyPropertiesValidated event to self on passkey form submit', function () {
    livewire(PasskeyAuthentication::class)
        ->callTableAction('addPasskey', data: ['name' => 'Test Passkey'])
        ->assertDispatched('passkeyPropertiesValidated');
});

it('closes passkey modal when registration fails', function () {
    livewire(PasskeyAuthentication::class)
        ->callTableAction('addPasskey', data: ['name' => 'Test Passkey'])
        ->call('handlePasskeyRegistrationFailed')
        ->assertTableActionNotMounted('addPasskey');
});

it('requires compatible webauthn-lib version', function () {
    $composerJson = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

    // webauthn-lib 5.2.3+ has breaking change: PublicKeyCredentialSource → CredentialRecord
    // Must stay pinned to 5.2.2 for spatie/laravel-passkeys compatibility
    expect($composerJson['require']['web-auth/webauthn-lib'])->toBe('5.2.2');
});
