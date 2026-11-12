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
            $email = strtolower(trim((string) $email));

            if ($email !== '') {
                return $email;
            }
        }

        return null;
    }
}
