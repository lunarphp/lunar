<?php

namespace Lunar\Observers;

use Lunar\Base\Purchasable;
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\OrderLine;

class OrderLineObserver
{
    /**
     * Handle the OrderLine "creating" event.
     *
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
     * @return void
     */
    public function updating(OrderLine $orderLine)
    {
        if ($orderLine->type != 'shipping' && ! $orderLine->purchasable instanceof Purchasable) {
            throw new NonPurchasableItemException($orderLine->purchasable_type);
        }
    }
}
