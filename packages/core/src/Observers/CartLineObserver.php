<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

class CartLineObserver
{
    /**
     * Handle the CartLine "creating" event.
     *
     * @return void
     */
    public function creating(CartLineContract $cartLine)
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
    public function updating(CartLineContract $cartLine)
    {
        /** @var CartLine $cartLine */
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }
}
