<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;

/**
 * Give an order its initial fulfilments. Walk the registered methods in
 * priority order, handing each the still-unclaimed fulfillable lines; each
 * claims a subset, and a method that claims >= 1 line gets one fulfilment (in its
 * `defaultState()`) covering them at full quantity. One method, an all-physical
 * order → exactly one fulfilment (today's behaviour); a basket of a delivered good
 * + a licence key + a collection item → a fulfilment per claiming method.
 *
 * Idempotent — a no-op if the order already has any fulfilment, or has no
 * fulfillable lines. Returns the first fulfilment created (or null). The merchant
 * never creates fulfilments by hand; they split these or merge back.
 */
class EnsureInitialFulfilment implements EnsuresInitialFulfilment
{
    public function __construct(
        protected CreatesFulfilment $createFulfilment,
        protected FulfilmentMethodManifest $methods,
    ) {}

    public function execute(OrderContract $order): ?Fulfilment
    {
        /** @var Order $order */
        if ($order->fulfilments()->exists()) {
            return null;
        }

        $unclaimed = $order->fulfillableLines()->get();

        if ($unclaimed->isEmpty()) {
            return null;
        }

        $first = null;

        foreach ($this->methods->all() as $method) {
            if ($unclaimed->isEmpty()) {
                break;
            }

            $claimed = $method->claim($order, $unclaimed);

            if ($claimed->isEmpty()) {
                continue;
            }

            $lines = $claimed->mapWithKeys(fn ($line) => [$line->id => $line->quantity])->all();

            $fulfilment = $this->createFulfilment->execute($order, $lines, ['method' => $method->getKey()]);

            $first ??= $fulfilment;

            $claimedIds = $claimed->pluck('id')->all();
            $unclaimed = $unclaimed->reject(fn ($line) => in_array($line->id, $claimedIds, true))->values();
        }

        return $first;
    }
}
