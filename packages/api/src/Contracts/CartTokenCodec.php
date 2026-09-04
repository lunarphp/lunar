<?php

namespace Lunar\Api\Contracts;

use Lunar\Core\Models\Cart;

/**
 * Encodes the stateless `X-Lunar-Cart` token a guest carries between requests,
 * and decodes it back to the cart's `public_id` when the signature and expiry
 * hold.
 */
interface CartTokenCodec
{
    public function encode(Cart $cart): string;

    /** The cart `public_id`, or null when the token is malformed, forged or expired. */
    public function decode(string $token): ?string;
}
