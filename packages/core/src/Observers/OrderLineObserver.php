<?php

namespace GetCandy\Observers;

use GetCandy\Base\Purchasable;
use GetCandy\Exceptions\NonPurchasableItemException;
use GetCandy\Models\OrderLine;

class OrderLineObserver
{
    /**
     * Handle the OrderLine "creating" event.
     *
     * @param  \GetCandy\Models\OrderLine  $orderLine
     * @return void
     */
    public function creating(OrderLine $orderLine)
    {
        if ($orderLine->type != 'shipping' && ! $orderLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($orderLine->purchasable_type);
        }
    }

    /**
     * Handle the OrderLine "updated" event.
     *
     * @param  \GetCandy\Models\OrderLine  $orderLine
     * @return void
     */
    public function updating(OrderLine $orderLine)
    {
        if (! $orderLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($orderLine->purchasable_type);
        }
    }
}
