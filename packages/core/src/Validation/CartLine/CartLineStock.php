<?php

namespace Lunar\Validation\CartLine;

use Lunar\Validation\BaseValidator;

class CartLineStock extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $quantity = $this->parameters['quantity'] ?? 0;
        $lineItem = $this->parameters['purchasable'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $cart = $this->parameters['cart'] ?? null;

        if ($cartLineId && ! $lineItem && $cart) {
            $lineItem = $cart->lines->first(
                fn ($cartLine) => $cartLine->id == $cartLineId
            )?->purchasable;
        }

        if ($lineItem->purchasable == 'always') {
            return $this->pass();
        }

        if (
            $lineItem->purchasable == 'in_stock' && $quantity < $lineItem->stock ||
            $lineItem->purchasable == 'in_stock_or_backorder' && $quantity < ($lineItem->stock + $lineItem->backorder)
        ) {
            $this->fail('cart', 'Item is not in stock at this quantity.');
        }

        return $this->pass();
    }
}
