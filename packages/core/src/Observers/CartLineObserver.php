<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\CartLine;

class CartLineObserver
{
    /**
     * Handle the CartLine "creating" event.
     *
     * @return void
     */
    public function creating(CartLine $cartLine)
    {
        /** @var CartLine $cartLine */
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }

    /**
     * Handle the CartLine "updated" event.
     *
     * @return void
     */
    public function updating(CartLine $cartLine)
    {
        /** @var CartLine $cartLine */
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }

    public function saved(CartLine $cartLine): void
    {
        $this->invalidateCartTotals($cartLine);
    }

    public function deleted(CartLine $cartLine): void
    {
        $this->invalidateCartTotals($cartLine);
    }

    /**
     * Any line write stales the cart's persisted totals, except a write made
     * by the calculation pipeline itself (an automatic reward line), which is
     * part of the snapshot about to be written. Cart::lines() chaperones the
     * parent onto its lines so that instance is the one checked here.
     */
    protected function invalidateCartTotals(CartLine $cartLine): void
    {
        $cart = $cartLine->cart;

        if (! $cart || $cart->isCalculating()) {
            return;
        }

        $cart->invalidateTotals();
    }
}
