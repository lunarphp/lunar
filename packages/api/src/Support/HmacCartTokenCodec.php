<?php

namespace Lunar\Api\Support;

use Lunar\Api\Contracts\CartTokenCodec;
use Lunar\Core\Models\Cart;

/**
 * `base64url(public_id . '.' . expiry . '.' . hmac_sha256(public_id . '.' . expiry))`
 * keyed by the app key: unguessable, stateless, and not forgeable from a
 * leaked `public_id` alone.
 */
final class HmacCartTokenCodec implements CartTokenCodec
{
    private readonly string $key;

    public function __construct(string $appKey, private readonly int $ttlDays)
    {
        $this->key = str_starts_with($appKey, 'base64:')
            ? (string) base64_decode(substr($appKey, 7), true)
            : $appKey;
    }

    public function encode(Cart $cart): string
    {
        $payload = $cart->public_id.'.'.now()->addDays($this->ttlDays)->getTimestamp();

        return $this->base64UrlEncode($payload.'.'.$this->sign($payload));
    }

    public function decode(string $token): ?string
    {
        $decoded = $this->base64UrlDecode($token);

        if ($decoded === null) {
            return null;
        }

        $parts = explode('.', $decoded);

        if (count($parts) !== 3) {
            return null;
        }

        [$publicId, $expiry, $signature] = $parts;

        if ($publicId === '' || ! ctype_digit($expiry) || (int) $expiry < now()->getTimestamp()) {
            return null;
        }

        if (! hash_equals($this->sign("{$publicId}.{$expiry}"), $signature)) {
            return null;
        }

        return $publicId;
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->key);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
