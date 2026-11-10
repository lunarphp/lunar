<?php

namespace Lunar\Observers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Base\Purchasable;
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\Contracts\OrderLine as OrderLineContract;
use Lunar\Models\OrderLine;

class OrderLineObserver
{
    /**
     * Handle the OrderLine "creating" event.
     */
    public function creating(OrderLineContract $orderLine): void
    {
        /** @var OrderLine $orderLine */
        // Self-describing line (shipping, ad-hoc charge) — no morph to validate.
        if ($orderLine->purchasable_type === null) {
            return;
        }

        $purchasableModel = class_exists($orderLine->purchasable_type) ?
            $orderLine->purchasable_type :
            Relation::getMorphedModel($orderLine->purchasable_type);

        if (! $purchasableModel || ! in_array(Purchasable::class, class_implements($purchasableModel, true))) {
            throw new NonPurchasableItemException($purchasableModel);
        }
    }

    /**
     * Handle the OrderLine "updated" event.
     */
    public function updating(OrderLineContract $orderLine): void
    {
        // Self-describing line (shipping, ad-hoc charge) — no morph to validate.
        if ($orderLine->purchasable_type === null) {
            return;
        }

        $purchasableModel = class_exists($orderLine->purchasable_type) ?
            $orderLine->purchasable_type :
            Relation::getMorphedModel($orderLine->purchasable_type);

        if (! $purchasableModel || ! in_array(Purchasable::class, class_implements($purchasableModel, true))) {
            throw new NonPurchasableItemException($purchasableModel);
        }
    }
}
