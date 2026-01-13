# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Filament plugin that adds Two-Factor Authentication (2FA) to Filament v3 applications. It supports both Google 2FA (TOTP) and Passkey authentication using the spatie/laravel-passkeys package under the hood.

## Development Commands

### Testing
```bash
composer test              # Run the full test suite
vendor/bin/pest           # Run tests directly
vendor/bin/pest --filter TestName  # Run specific test
```

### Code Quality
```bash
composer format           # Fix code style with Pint
vendor/bin/pint          # Run Pint directly
composer analyse          # Run PHPStan static analysis
vendor/bin/phpstan analyse  # Run PHPStan directly
vendor/bin/rector process # Run Rector code modernization
vendor/bin/rector --dry-run # Preview Rector changes without applying
```

### Asset Building
```bash
npm run dev              # Watch styles and scripts for development
npm run build            # Build production assets (styles + scripts + purge)
npm run dev:styles       # Watch Tailwind CSS only
npm run dev:scripts      # Watch JavaScript only
npm run build:styles     # Build and minify CSS
npm run build:scripts    # Build JavaScript
npm install              # Also installs Husky git hooks via prepare script
```

## Architecture

### Plugin Registration
The `TwoFactorAuthenticationPlugin` class (src/TwoFactorAuthenticationPlugin.php) is the entry point. It:
- Registers three custom routes: `/two-factor-challenge`, `/two-factor-recovery`, `/two-factor-setup`
- Registers passkey authentication routes under `/passkeys/*`
- Adds authentication middleware (`TwoFactorChallenge` and optionally `ForceTwoFactorSetup`)
- Adds optional user menu item for 2FA settings
- Injects passkey login UI via render hook when passkey authentication is enabled

### Authentication Flow
1. **Setup**: User enables 2FA via `Setup` page, which generates a secret and recovery codes
2. **Challenge**: On subsequent logins, `TwoFactorChallenge` middleware redirects to the challenge page if 2FA is enabled but not passed
3. **Verification**: User provides TOTP code, which is verified against their encrypted secret
4. **Session Management**: Once passed, a session value is set (`login_2fa_challenge_passed_{user_id}`) to bypass future challenges until logout

### Key Components

**Trait: TwoFactorAuthenticatable** (src/TwoFactorAuthenticatable.php)
- Applied to the User model
- Provides methods for QR code generation, recovery code management, and challenge status
- Implements `InteractsWithPasskeys` from spatie/laravel-passkeys

**Middleware: TwoFactorChallenge** (src/Middleware/TwoFactorChallenge.php)
- Checks if user has enabled 2FA and hasn't passed the challenge
- Redirects to `/two-factor-challenge` if needed
- Bypasses logout routes

**Middleware: ForceTwoFactorSetup** (src/Middleware/ForceTwoFactorSetup.php)
- Optional middleware that forces all users to enable 2FA
- Redirects to setup page if not enabled

**Actions** (src/Actions/)
- `EnableTwoFactorAuthentication`: Generates secret and recovery codes
- `ConfirmTwoFactorAuthentication`: Validates setup and marks as confirmed
- `DisableTwoFactorAuthentication`: Clears 2FA data
- `GenerateNewRecoveryCodes`: Regenerates recovery codes
- `RecoveryCode`: Static helper for generating individual recovery codes

**Pages** (src/Pages/)
- `Challenge`: TOTP code entry page after login
- `Recovery`: Recovery code entry page (alternative to TOTP)
- `Setup`: 2FA configuration page with QR code and confirmation
- `BaseSimplePage`: Extends Filament's SimplePage with rate limiting

**Livewire Components** (src/Livewire/)
- `TwoFactorAuthentication`: Main 2FA settings component (enable/disable/recovery codes)
- `PasskeyAuthentication`: Passkey management component
- `BaseLivewireComponent`: Base class with password confirmation logic

### Database Schema
The plugin adds columns to the users table via migration stub:
- `two_factor_secret` (text, nullable, encrypted)
- `two_factor_recovery_codes` (text, nullable, encrypted)
- `two_factor_confirmed_at` (timestamp, nullable)

The passkeys table is managed by spatie/laravel-passkeys.

### Events
All events extend `TwoFactorAuthenticationEvent` and include the user:
- `TwoFactorAuthenticationChallenged`: Dispatched when challenge page loads
- `TwoFactorAuthenticationFailed`: Dispatched on invalid code/recovery code
- `ValidTwoFactorAuthenticationCodeProvided`: Dispatched on successful TOTP validation
- `ValidTwoFactorRecoveryCodeProvided`: Dispatched on successful recovery code validation
- `TwoFactorAuthenticationConfirmed`: Dispatched when user confirms during setup
- `TwoFactorAuthenticationEnabled`: Dispatched when 2FA is fully enabled
- `TwoFactorAuthenticationDisabled`: Dispatched when 2FA is disabled
- `RecoveryCodeReplaced`: Dispatched when a recovery code is used and replaced
- `RecoveryCodesGenerated`: Dispatched when recovery codes are regenerated

### Service Provider
`TwoFactorAuthenticationServiceProvider` (src/TwoFactorAuthenticationServiceProvider.php):
- Registers the plugin with Filament
- Publishes views and migrations
- Binds `TwoFactorAuthenticationProvider` contract to concrete implementation
- Loads translations

### Configuration
The plugin is configured via method chaining in the panel configuration:
```php
TwoFactorAuthenticationPlugin::make()
    ->enableTwoFactorAuthentication()  // Enable Google 2FA
    ->enablePasskeyAuthentication()    // Enable Passkey
    ->forceTwoFactorSetup()            // Force all users to enable 2FA
    ->addTwoFactorMenuItem()           // Add 2FA menu item
```

**Configuration Method Signatures:**

`enableTwoFactorAuthentication(condition, challengeMiddleware)`:
- `condition`: bool|Closure - Enable/disable Google 2FA (default: true)
- `challengeMiddleware`: class-string - Middleware class for 2FA challenge (default: TwoFactorChallenge::class)

`enablePasskeyAuthentication(condition, showLoginButton, enableAutofill)`:
- `condition`: bool|Closure - Enable/disable passkey authentication (default: true)
- `showLoginButton`: bool|Closure - Show "Sign in with passkey" button on login page (default: true)
- `enableAutofill`: bool|Closure - Enable passkey autofill in email input field (default: false)

`forceTwoFactorSetup(condition, requiresPassword, forceMiddleware)`:
- `condition`: bool|Closure - Force all users to set up 2FA (default: true)
- `requiresPassword`: bool|Closure - Require password confirmation during setup (default: false)
- `forceMiddleware`: class-string - Middleware class to enforce setup (default: ForceTwoFactorSetup::class)

`addTwoFactorMenuItem(condition, label, icon)`:
- `condition`: bool|Closure - Show 2FA in user menu (default: true)
- `label`: string - Menu item label (default: '2FA')
- `icon`: string - Heroicon name (default: 'heroicon-s-key')

### Testing
Tests use Pest and Orchestra Testbench with a custom `TestCase` that:
- Sets up all required Filament service providers
- Creates an in-memory SQLite database
- Runs migrations for 2FA columns and passkeys table
- Creates a default test user via `User::createDefault()`

**Architecture Tests** (tests/ArchTest.php):
Using `pestphp/pest-plugin-arch`, the following architecture constraints are enforced:
- No debugging functions (`dd`, `dump`, `ray`) in production code
- No direct `env()` helper usage (use config files instead)
- All Actions, Events, and Middleware are classes
- All Middleware have a `handle` method
- All Pages extend `BaseSimplePage` and are not final
- All Livewire components extend `Livewire\Component`

### Code Quality Tools

**PHPStan** (phpstan.neon.dist):
- Level 4 static analysis
- Analyzes `src` and `database` directories
- Uses baseline file for known issues

**Rector** (rector.php):
- Configured rule sets: `deadCode`, `codeQuality`, `typeDeclarations`, `privatization`, `earlyReturn`
- PHP version sets enabled
- Skips certain rules that don't fit the codebase style

**Pre-commit Hooks** (.husky/pre-commit):
Automatically runs on every commit:
1. Pint on staged PHP files (auto-fixes and re-stages)
2. PHPStan analysis
3. Rector dry-run check
4. Prettier for JS/CSS files via lint-staged

### CI/CD Workflows

**Tests** (.github/workflows/run-tests.yml):
- Runs on PHP 8.2, 8.3, 8.4
- Tests against Laravel 11.* and 12.*
- Includes `composer audit` for security vulnerability scanning

**Static Analysis** (.github/workflows/static-analysis.yml):
- Runs PHPStan at level 4
- Runs Rector in dry-run mode
- Triggers on push to main/2.x branches and PRs

**Code Style** (.github/workflows/fix-php-code-style-issues.yml):
- Auto-applies Pint formatting and commits changes

## Important Notes

- The plugin requires Filament v3.0+, PHP 8.2+, and Laravel 11+
- Tested on PHP 8.2, 8.3, and 8.4 with Laravel 11 and 12
- Secrets and recovery codes are always stored encrypted in the database
- Recovery codes are one-time use and automatically replaced after successful use
- Passkey authentication bypasses 2FA challenge if both are enabled
- The challenge session is tied to the specific user's secret (hashed comparison)
- **Important**: WebAuthn library is pinned to version 5.2.2 for compatibility with spatie/laravel-passkeys (5.2.3+ has breaking changes)
- Run `npm install` after cloning to set up Husky pre-commit hooks
