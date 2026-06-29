<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Validation\Fulfilment\FulfilmentQuantity;

/**
 * Create a `Fulfilment` covering specific order lines, for a given fulfilment
 * method (defaulting to `shipping`). It stamps the method and that method's
 * `defaultState()` as the initial state.
 *
 * Validates the section A quantity invariant and creates the fulfilment and its
 * lines in one transaction (the rule locks the order-line rows, serialising
 * concurrent writes against the same lines), then fires `FulfilmentCreated`.
 * The fulfilment observer recomputes the order's `fulfilment_status`.
 */
class CreateFulfilment implements CreatesFulfilment
{
    public function __construct(
        protected FulfilmentQuantity $fulfilmentQuantity,
        protected FulfilmentMethodManifest $methods,
    ) {}

    public function execute(Order $order, array $lines, array $attributes = []): Fulfilment
    {
        /** @var Order $order */
        $methodKey = $attributes['method'] ?? Shipping::KEY;
        $method = $this->methods->get($methodKey)
            ?? throw new \InvalidArgumentException("Fulfilment method [{$methodKey}] is not registered.");

        $fulfilment = DB::transaction(function () use ($order, $lines, $attributes, $method) {
            // Validate inside the transaction: the rule locks each order-line
            // row, so a concurrent create against the same lines waits here
            // rather than both reading the same covered total and over-fulfilling.
            $this->fulfilmentQuantity->validate($order, $lines);

            /** @var Fulfilment $fulfilment */
            $fulfilment = $order->fulfilments()->create([
                'reference' => $attributes['reference'] ?? null,
                'location_id' => $attributes['location_id'] ?? $this->defaultLocationId(),
                'method' => $method->getKey(),
                'state' => $method->defaultState()::$name,
                'notes' => $attributes['notes'] ?? null,
                'meta' => $attributes['meta'] ?? null,
            ]);

            foreach ($lines as $orderLineId => $quantity) {
                $fulfilment->lines()->create([
                    'order_line_id' => $orderLineId,
                    'quantity' => $quantity,
                ]);
            }

            return $fulfilment;
        });

        FulfilmentCreated::dispatch($fulfilment);

        return $fulfilment->refresh();
    }

    /**
     * Whether there is any outstanding fulfillable quantity left to fulfil — used
     * to gate the "create fulfilment" action in the UI.
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        $fulfilmentQuantity = new FulfilmentQuantity;

        foreach ($order->fulfillableLines()->get() as $line) {
            if ($fulfilmentQuantity->coveredQuantity($order, $line->id) < $line->quantity) {
                return true;
            }
        }

        return false;
    }

    /**
     * The location a fulfilment is assigned to when none is given. Prefers the
     * default location, then any location, and falls back to creating the
     * `Default` location so the (required) `location_id` is always resolvable.
     */
    protected function defaultLocationId(): int
    {
        return Location::query()->where('default', true)->value('id')
            ?? Location::query()->orderBy('id')->value('id')
            ?? Location::query()->create(['name' => 'Default', 'handle' => 'default', 'default' => true])->id;
    }
}
