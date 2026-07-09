<?php

namespace Lunar\Panel\Auth;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Core\Models\Staff;
use PragmaRX\Google2FA\Google2FA;
use SensitiveParameter;

/**
 * TOTP app authentication for panel staff.
 *
 * Storage and verification semantics deliberately match Filament v5's
 * app-authentication feature so a staff member's 2FA works in both panels:
 * a 16-char base32 secret behind the model's `encrypted` cast, eight
 * bcrypt-hashed single-use recovery codes behind `encrypted:array`, and a
 * TOTP verification window of 8 periods (~4 minutes) each side.
 */
class AppAuthentication
{
    protected int $codeWindow = 8;

    protected int $recoveryCodeCount = 8;

    public function __construct(
        protected Google2FA $google2fa,
        protected Cache $cache,
        protected Hasher $hasher,
        protected ConnectionInterface $db,
    ) {}

    public function isEnabled(Staff $staff): bool
    {
        return filled($staff->app_authentication_secret);
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(16);
    }

    public function verifyCode(#[SensitiveParameter] string $secret, string $code, bool $preventReuse = false): bool
    {
        if (! $preventReuse) {
            return (bool) $this->google2fa->verifyKey($secret, $code, $this->codeWindow);
        }

        $cacheKey = 'lunar.panel.two_factor_codes.'.md5($secret.$code);

        $timestamp = $this->google2fa->verifyKeyNewer(
            $secret,
            $code,
            $this->cache->get($cacheKey),
            $this->codeWindow,
        );

        if ($timestamp === false) {
            return false;
        }

        $this->cache->put(
            $cacheKey,
            $timestamp === true ? $this->google2fa->getTimestamp() : $timestamp,
            ($this->codeWindow + 1) * 60,
        );

        return true;
    }

    /**
     * @return array<int, string> plaintext codes — hash with hashRecoveryCodes() before persisting
     */
    public function generateRecoveryCodes(): array
    {
        return Collection::times(
            $this->recoveryCodeCount,
            fn (): string => Str::random(10).'-'.Str::random(10),
        )->all();
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<int, string>
     */
    public function hashRecoveryCodes(#[SensitiveParameter] array $codes): array
    {
        return array_map(fn (string $code): string => $this->hasher->make($code), $codes);
    }

    /**
     * A matched code is removed and the remaining set re-persisted, so each
     * code works exactly once. Row-locked to avoid double spends.
     */
    public function verifyAndConsumeRecoveryCode(Staff $staff, #[SensitiveParameter] string $code): bool
    {
        return (bool) $this->db->transaction(function () use ($staff, $code): bool {
            $fresh = $staff->newQuery()->lockForUpdate()->findOrFail($staff->getKey());

            $codes = $fresh->app_authentication_recovery_codes ?? [];

            foreach ($codes as $index => $hashed) {
                if ($this->hasher->check($code, $hashed)) {
                    unset($codes[$index]);

                    $fresh->forceFill([
                        'app_authentication_recovery_codes' => array_values($codes),
                    ])->save();

                    return true;
                }
            }

            return false;
        });
    }

    public function qrCodeDataUri(string $issuer, string $holder, #[SensitiveParameter] string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl($issuer, $holder, $secret);

        $svg = (new Writer(
            new ImageRenderer(new RendererStyle(192, 0), new SvgImageBackEnd),
        ))->writeString($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
