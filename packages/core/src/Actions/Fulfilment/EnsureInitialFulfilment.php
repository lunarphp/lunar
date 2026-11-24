<?php

namespace Lunar\Core\Actions\Fulfilment;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;

/**
 * Give an order its initial fulfilments. Lines stamped with a registered
 * method at order creation go to that method first. Then walk the registered
 * methods in priority order, handing each the still-unclaimed fulfillable lines; each
 * claims a subset, and a method that claims >= 1 line gets one fulfilment (in its
 * `defaultState()`) covering them at full quantity. One method, an all-physical
 * order → exactly one fulfilment (today's behaviour); a basket of a delivered good
 * + a licence key + a pickup item → a fulfilment per claiming method.
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

    public function execute(Order $order): ?Fulfilment
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

        $explicit = $unclaimed->filter(fn (OrderLine $line) => $this->methods->get($line->fulfilment_method) !== null);

        foreach ($explicit->groupBy('fulfilment_method') as $key => $lines) {
            $fulfilment = $this->createFor($order, $this->methods->get($key), $lines);
            $first ??= $fulfilment;
        }

        $unclaimed = $unclaimed->diff($explicit)->values();

        foreach ($this->methods->all() as $method) {
            if ($unclaimed->isEmpty()) {
                break;
            }

            $claimed = $method->claim($order, $unclaimed);

            if ($claimed->isEmpty()) {
                continue;
            }

            $fulfilment = $this->createFor($order, $method, $claimed);
            $first ??= $fulfilment;

            $unclaimed = $unclaimed->diff($claimed)->values();
        }

        return $first;
    }

    /**
     * @param  Collection<int, OrderLine>  $lines
     */
    protected function createFor(Order $order, FulfilmentMethod $method, Collection $lines): Fulfilment
    {
        $attributes = ['method' => $method->getKey()];

        if ($method->getKey() === Pickup::KEY && $order->pickup_location_id) {
            $attributes['location_id'] = $order->pickup_location_id;
        }

        return $this->createFulfilment->execute(
            $order,
            $lines->mapWithKeys(fn (OrderLine $line) => [$line->id => $line->quantity])->all(),
            $attributes,
        );
    }
}
