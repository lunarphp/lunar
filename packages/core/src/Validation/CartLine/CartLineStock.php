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
        $purchasable = $this->parameters['purchasable'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $cart = $this->parameters['cart'] ?? null;

        if ($cartLineId && ! $purchasable && $cart) {
            $purchasable = $cart->lines->first(
                fn ($cartLine) => $cartLine->id == $cartLineId
            )?->purchasable;
        }

        return $purchasable->canBeFulfilledAtQuantity($quantity) ?
            $this->pass() :
            $this->fail('cart', 'Item is not available at this quantity.');
    }
}
