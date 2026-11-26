<?php

namespace Lunar\Core\Validation\Cart;

use Lunar\Core\Contracts\Actions\Discounts\ResolvesCustomerEmail;
use Lunar\Core\Models\Cart;
use Lunar\Core\Validation\BaseValidator;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;

/**
 * A discount limited per customer needs a customer to count against: a
 * signed-in user or an email. Counting is left to the discount conditions.
 */
class ValidateCustomerForDiscounts extends BaseValidator
{
    public function __construct(
        protected ResolvesCustomerEmail $resolvesCustomerEmail,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        /** @var Cart $cart */
        $cart = $this->parameters['cart'];

        $limited = ($cart->calculate()->discountBreakdown ?? collect())
            ->first(fn (DiscountBreakdown $breakdown) => (bool) $breakdown->discount->max_uses_per_user);

        if (! $limited || $cart->user_id || $this->resolvesCustomerEmail->execute($cart)) {
            return $this->pass();
        }

        return $this->fail('cart', __('lunar::exceptions.carts.customer_email_missing', [
            'discount' => $limited->discount->name,
        ]));
    }
}
