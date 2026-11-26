<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\ResolvesCustomerEmail;
use Lunar\Core\Models\Cart;

/**
 * The signed-in user's email, then the billing contact email, then the
 * shipping contact email; lower-cased and trimmed.
 */
class ResolveCustomerEmail implements ResolvesCustomerEmail
{
    public function execute(Cart $cart): ?string
    {
        $candidates = [
            $cart->user?->email,
            $cart->billingAddress?->contact_email,
            $cart->shippingAddress?->contact_email,
        ];

        foreach ($candidates as $email) {
            $email = static::normalize($email);

            if ($email !== null) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Lower-cased and trimmed, or null when blank.
     */
    public static function normalize(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }
}
