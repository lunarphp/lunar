<?php

namespace Lunar\Core\Validation\CartLine;

use Lunar\Core\Models\Cart;
use Lunar\Core\Validation\BaseValidator;

/**
 * Refuses a quantity change on a line a discount added. The calculation
 * sizes that line to the reward units its discount allocates, so a change
 * would be undone on the next calculation (spec remove-unearned-reward-lines).
 * A shopper who wants more adds the product, which gets a line of its own.
 * An update that leaves the quantity as it is, to change meta, passes.
 */
class CartLineAddedByDiscount extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        /** @var ?Cart $cart */
        $cart = $this->parameters['cart'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $quantity = $this->parameters['quantity'] ?? null;

        $line = $cart?->lines->first(fn ($cartLine) => $cartLine->id == $cartLineId);

        if (! $line?->isAddedByDiscount() || $quantity === null || (int) $quantity === $line->quantity) {
            return $this->pass();
        }

        return $this->fail('cart', __('lunar::exceptions.carts.line_added_by_discount'));
    }
}
