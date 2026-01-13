<?php

namespace Stephenjude\FilamentTwoFactorAuthentication;

use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;
use Stephenjude\FilamentTwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;

class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    /**
     * Create a new two factor authentication provider instance.
     */
    public function __construct(
        /**
         * The underlying library providing two factor authentication helper services.
         */
        protected Google2FA $engine,
        /**
         * The cache repository implementation.
         */
        protected ?Repository $cache = null
    )
    {
    }

    /**
     * Generate a new secret key.
     */
    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * Get the two factor authentication QR code URL.
     */
    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    /**
     * Verify the given code.
     */
    public function verify(string $secret, string $code): bool
    {
        if (is_int($customWindow = config('fortify-options.two-factor-authentication.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $key = 'fortify.2fa_codes.' . md5($code);

        $timestamp = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            $this->cache?->get($key)
        );

        if ($timestamp !== false) {
            if ($timestamp === true) {
                $timestamp = $this->engine->getTimestamp();
            }

            $this->cache?->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }
}
