<?php

namespace Lunar\Panel\Auth;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use SensitiveParameter;

/**
 * Email one-time-code fallback for staff without TOTP configured, so every
 * panel login carries a second factor one way or another.
 */
class EmailTwoFactor
{
    protected int $codeTtlSeconds = 600;

    protected int $cooldownSeconds = 30;

    public function __construct(
        protected Cache $cache,
        protected Hasher $hasher,
    ) {}

    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Sends a fresh code unless the resend cooldown is still active, in
     * which case nothing is generated, hashed, or sent.
     */
    public function send(Staff $staff): bool
    {
        if ($this->cooldownRemaining($staff) > 0) {
            return false;
        }

        $code = $this->generateCode();

        $this->cache->put($this->codeCacheKey($staff), $this->hasher->make($code), $this->codeTtlSeconds);
        $this->cache->put($this->cooldownCacheKey($staff), now()->addSeconds($this->cooldownSeconds)->getTimestamp(), $this->cooldownSeconds);

        $staff->notify(new TwoFactorEmailCode($code));

        return true;
    }

    /**
     * A wrong code is left in place so it can still be tried again until it
     * either matches or expires — the challenge's own rate limiter is what
     * bounds guessing, not consumption on failure.
     */
    public function verifyAndConsume(Staff $staff, #[SensitiveParameter] string $code): bool
    {
        $hash = $this->cache->get($this->codeCacheKey($staff));

        if (! $hash || ! $this->hasher->check($code, $hash)) {
            return false;
        }

        $this->cache->forget($this->codeCacheKey($staff));

        return true;
    }

    public function cooldownRemaining(Staff $staff): int
    {
        $expiresAt = $this->cache->get($this->cooldownCacheKey($staff));

        if (! $expiresAt) {
            return 0;
        }

        return max(0, $expiresAt - now()->getTimestamp());
    }

    protected function codeCacheKey(Staff $staff): string
    {
        return 'lunar-panel:email-otp:'.$staff->getKey();
    }

    protected function cooldownCacheKey(Staff $staff): string
    {
        return 'lunar-panel:email-otp-cooldown:'.$staff->getKey();
    }
}
