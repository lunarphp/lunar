<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ChangesFulfilmentMethod;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Location;

/**
 * Move outstanding quantities into a new fulfilment with a different method,
 * for example a delivery the customer now collects. The order lines keep the
 * method the customer requested. Core never charges or refunds the change.
 */
class ChangeFulfilmentMethod implements ChangesFulfilmentMethod
{
    public function __construct(
        protected FulfilmentMethodManifest $methods,
    ) {}

    public function execute(Fulfilment $fulfilment, string $method, ?Location $location = null, ?array $moves = null): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if (! self::canRun($fulfilment)) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_method_not_changeable'));
        }

        $target = $this->methods->get($method)
            ?? throw new FulfilmentException(__('lunar::exceptions.carts.fulfilment_method_unknown', ['method' => $method]));

        $order = $fulfilment->order;

        if ($target->requiresDelivery() && ! $order->shippingAddress()->exists()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_change_address_missing'));
        }

        $location ??= $target->getKey() === Pickup::KEY
            ? ($order->pickupLocation ?? Location::getDefault())
            : Location::getDefault();

        return DB::transaction(function () use ($fulfilment, $target, $location, $moves) {
            $sourceLines = $fulfilment->lines()->with('orderLine.purchasable')->lockForUpdate()->get()->keyBy('order_line_id');

            $moves ??= $sourceLines->map(fn (FulfilmentLine $line) => $line->quantity)->all();

            $this->guardMoves($sourceLines->all(), $moves, $target);

            /** @var Fulfilment $new */
            $new = $fulfilment->order->fulfilments()->create([
                'location_id' => $location->id ?? $fulfilment->location_id,
                'method' => $target->getKey(),
                'state' => $target->defaultState()::$name,
            ]);

            foreach ($moves as $orderLineId => $quantity) {
                $sourceLine = $sourceLines->get($orderLineId);
                $remaining = $sourceLine->quantity - $quantity;

                $remaining > 0
                    ? $sourceLine->update(['quantity' => $remaining])
                    : $sourceLine->delete();

                $new->lines()->create(['order_line_id' => $orderLineId, 'quantity' => $quantity]);
            }

            if (! $fulfilment->lines()->exists()) {
                $fulfilment->delete();
            }

            FulfilmentCreated::dispatch($new);

            return $new->refresh();
        });
    }

    /**
     * Whether the fulfilment can still change method: nothing handed over yet.
     */
    public static function canRun(Fulfilment $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->category() === FulfilmentStateCategory::Outstanding;
    }

    /**
     * @param  array<int|string, FulfilmentLine>  $sourceLines
     * @param  array<int|string, int>  $moves
     */
    protected function guardMoves(array $sourceLines, array $moves, FulfilmentMethod $target): void
    {
        foreach ($moves as $orderLineId => $quantity) {
            $sourceLine = $sourceLines[$orderLineId] ?? null;

            if (! $sourceLine || $quantity < 1 || $quantity > $sourceLine->quantity) {
                throw new FulfilmentException(__('lunar::exceptions.fulfilment_split_quantity', ['line' => $orderLineId]));
            }

            $purchasable = $sourceLine->orderLine->purchasable;

            if ($purchasable && ! $this->methods->supporting($purchasable)->has($target->getKey())) {
                throw new FulfilmentException(__('lunar::exceptions.carts.fulfilment_method_unsupported', [
                    'identifier' => $purchasable->getIdentifier(),
                    'method' => $target->getLabel(),
                ]));
            }
        }
    }
}
