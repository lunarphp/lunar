<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;

/**
 * Give an order its initial fulfilment: a single parcel covering every
 * fulfillable line at full quantity (the Shopify "split-down" starting point).
 *
 * Idempotent — a no-op if the order already has any fulfilment, or has no
 * fulfillable lines (orders with nothing to ship get no parcel). The merchant
 * never creates fulfilments by hand; they split this one or merge back.
 */
class EnsureInitialFulfilment implements EnsuresInitialFulfilment
{
    public function __construct(
        protected CreatesFulfilment $createFulfilment,
    ) {}

    public function execute(OrderContract $order): ?Fulfilment
    {
        /** @var Order $order */
        if ($order->fulfilments()->exists()) {
            return null;
        }

        $lines = $order->fulfillableLines()->get()
            ->mapWithKeys(fn ($line) => [$line->id => $line->quantity])
            ->all();

        if ($lines === []) {
            return null;
        }

        return $this->createFulfilment->execute($order, $lines);
    }
}
