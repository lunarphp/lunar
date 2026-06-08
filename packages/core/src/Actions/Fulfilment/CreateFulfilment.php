<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Validation\Fulfilment\FulfilmentQuantity;

/**
 * Create a `Fulfilment` (default `Pending`) covering specific order lines.
 *
 * Validates the §A quantity invariant before writing, then creates the
 * fulfilment and its lines in a transaction and fires `FulfilmentCreated`.
 * The fulfilment observer recomputes the order's `fulfilment_status`.
 */
final class CreateFulfilment implements CreatesFulfilment
{
    public function __construct(
        protected FulfilmentQuantity $fulfilmentQuantity,
    ) {}

    public function execute(OrderContract $order, array $lines, array $attributes = []): Fulfilment
    {
        /** @var Order $order */
        $this->fulfilmentQuantity->validate($order, $lines);

        $fulfilment = DB::transaction(function () use ($order, $lines, $attributes) {
            /** @var Fulfilment $fulfilment */
            $fulfilment = $order->fulfilments()->create([
                'reference' => $attributes['reference'] ?? null,
                'location_id' => $attributes['location_id'] ?? $this->defaultLocationId(),
                'state' => 'pending',
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
     * Whether there is any outstanding physical quantity left to fulfil — used
     * to gate the "create fulfilment" action in the UI.
     */
    public static function canRun(OrderContract $order): bool
    {
        /** @var Order $order */
        $fulfilmentQuantity = new FulfilmentQuantity;

        foreach ($order->physicalLines()->get() as $line) {
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
