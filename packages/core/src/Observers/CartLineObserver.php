<?php

namespace GetCandy\Observers;

use GetCandy\Base\Purchasable;
use GetCandy\Exceptions\NonPurchasableItemException;
use GetCandy\Models\CartLine;

class CartLineObserver
{
    /**
     * Handle the CartLine "creating" event.
     *
     * @param  \App\Models\CartLine  $user
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
     * @param  \App\Models\CartLine  $user
     * @return void
     */
    public function updating(CartLine $cartLine)
    {
        if (! $cartLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($cartLine->purchasable_type);
        }
    }
}
