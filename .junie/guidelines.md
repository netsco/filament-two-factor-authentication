# Filament Two-Factor Authentication Development Guidelines

This document provides essential information for developers working on the Filament Two-Factor Authentication package.

## Build/Configuration Instructions

### Installation for Development

1. Clone the repository:
   ```bash
   git clone https://github.com/stephenjude/filament-two-factor-authentication.git
   cd filament-two-factor-authentication
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Install the package migrations:
   ```bash
   php artisan filament-two-factor-authentication:install
   ```

### Development Environment Setup

This package is designed to be developed as a Laravel package. For local development and testing:

1. Use Laravel's package development approach with a local installation:
   ```bash
   composer config repositories.local '{"type": "path", "url": "../filament-two-factor-authentication"}' --file composer.json
   composer require stephenjude/filament-two-factor-authentication:@dev
   ```

2. For frontend assets, compile using:
   ```bash
   npm run dev
   ```

## Testing Information

### Testing Configuration

The package uses [Pest PHP](https://pestphp.com/) for testing, which is built on top of PHPUnit with a more expressive syntax.

Key testing files:
- `phpunit.xml.dist` - PHPUnit configuration
- `tests/Pest.php` - Pest configuration
- `tests/TestCase.php` - Base test case with setup for all tests

### Running Tests

Run all tests:
```bash
composer test
```

Run a specific test file:
```bash
vendor/bin/pest tests/path/to/test.php
```

Run tests with coverage report:
```bash
composer test-coverage
```

### Creating New Tests

1. Create a new test file in the `tests` directory with a descriptive name ending in `Test.php`
2. Use Pest's expressive syntax for writing tests:

```php
<?php

use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

it('can do something specific', function () {
    // Arrange
    $plugin = TwoFactorAuthenticationPlugin::make();
    
    // Act
    $result = $plugin->someMethod();
    
    // Assert
    expect($result)->toBeTrue();
});
```

3. Group related tests in the same file
4. Use descriptive test names that explain what the test is verifying

### Test Example

Here's a simple test that verifies the plugin can be instantiated and configured:

```php
<?php

use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

it('can instantiate the plugin', function () {
    $plugin = TwoFactorAuthenticationPlugin::make();
    
    expect($plugin)->toBeInstanceOf(TwoFactorAuthenticationPlugin::class);
});

it('can enable and disable two factor authentication', function () {
    $plugin = TwoFactorAuthenticationPlugin::make();
    
    // Initially disabled
    expect($plugin->hasEnabledTwoFactorAuthentication())->toBeFalse();
    
    // Enable
    $plugin->enableTwoFactorAuthentication();
    expect($plugin->hasEnabledTwoFactorAuthentication())->toBeTrue();
    
    // Disable by enabling with false condition
    $plugin->enableTwoFactorAuthentication(false);
    expect($plugin->hasEnabledTwoFactorAuthentication())->toBeFalse();
});
```

## Additional Development Information

### Code Style

The project uses [Laravel Pint](https://github.com/laravel/pint) for code styling, which is a wrapper around PHP-CS-Fixer with Laravel defaults.

Format code using:
```bash
composer format
```

### Static Analysis

The project uses [PHPStan](https://phpstan.org/) for static analysis:

```bash
composer analyse
```

### Package Architecture

The package follows a standard Laravel package structure:

- `src/` - Main package code
  - `Livewire/` - Livewire components
  - `Pages/` - Filament pages
  - `Middleware/` - Laravel middleware
- `database/migrations/` - Database migrations
- `resources/views/` - Blade views
- `tests/` - Test files

### Key Components

1. **TwoFactorAuthenticationPlugin** - The main plugin class that integrates with Filament panels
2. **TwoFactorAuthenticationServiceProvider** - The Laravel service provider for the package
3. **Livewire Components**:
   - `TwoFactorAuthentication` - Component for managing 2FA
   - `PasskeyAuthentication` - Component for managing passkeys
4. **Pages**:
   - `Challenge` - 2FA challenge page
   - `Recovery` - Recovery code page
   - `Setup` - 2FA setup page

### Events

The package dispatches several events that can be listened to in your application:

- `TwoFactorAuthenticationChallenged`
- `TwoFactorAuthenticationFailed`
- `ValidTwoFactorAuthenticationCodeProvided`
- `TwoFactorAuthenticationConfirmed`
- `TwoFactorAuthenticationEnabled`
- `TwoFactorAuthenticationDisabled`
- `RecoveryCodeReplaced`
- `RecoveryCodesGenerated`

### Dependencies

Key dependencies:
- `filament/filament` - Filament admin panel
- `pragmarx/google2fa` - Google 2FA implementation
- `bacon/bacon-qr-code` - QR code generation
- `spatie/laravel-passkeys` - Passkey authentication
- `spatie/laravel-package-tools` - Laravel package development tools

### Publishing Views

If you need to customize the package views:

```bash
php artisan vendor:publish --tag="filament-two-factor-authentication-views"
```
