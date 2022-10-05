<?php

namespace Lunar\Observers;

use Lunar\Base\Purchasable;
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\CartLine;

class CartLineObserver
{
    /**
     * Handle the CartLine "creating" event.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @return void
     */
    public function creating(CartLine $cartLine)
    {
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }

    /**
     * Handle the CartLine "updated" event.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @return void
     */
    public function updating(CartLine $cartLine)
    {
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }
}
