<?php

namespace Lunar\Core\Observers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Validation\Order\OrderLineQuantity;

class OrderLineObserver
{
    public function __construct(
        protected OrderLineQuantity $orderLineQuantity,
    ) {}

    /**
     * Handle the OrderLine "creating" event.
     */
    public function creating(OrderLine $orderLine): void
    {
        $this->assertPurchasable($orderLine);
    }

    /**
     * Handle the OrderLine "updating" event.
     */
    public function updating(OrderLine $orderLine): void
    {
        /** @var OrderLine $orderLine */
        $this->assertPurchasable($orderLine);

        // The line's quantity may not drop below what fulfilments already
        // cover (the section A invariant, protected from the order-line side).
        if ($orderLine->isDirty('quantity')) {
            $this->orderLineQuantity->validate($orderLine, (int) $orderLine->quantity);
        }
    }

    /**
     * Ensure the order line references a purchasable model.
     */
    protected function assertPurchasable(OrderLine $orderLine): void
    {
        /** @var OrderLine $orderLine */
        $purchasableModel = class_exists($orderLine->purchasable_type) ?
            $orderLine->purchasable_type :
            Relation::getMorphedModel($orderLine->purchasable_type);

        if (! $purchasableModel || ! in_array(Purchasable::class, class_implements($purchasableModel, true))) {
            throw new NonPurchasableItemException($purchasableModel);
        }
    }
}
