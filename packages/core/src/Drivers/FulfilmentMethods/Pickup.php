<?php

namespace Lunar\Core\Drivers\FulfilmentMethods;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\PickedUp;
use Lunar\Core\States\Fulfilment\ReadyForPickup;
use Lunar\Core\States\Fulfilment\Returned;

/**
 * Click-and-collect / trade-counter pickup. Claims physical lines when the
 * order's chosen shipping option is a pickup (`pickup === true`, persisted
 * onto the shipping line at order creation), so the fulfilment presents "ready
 * for pickup → picked up" instead of ship-and-track. No tracking.
 */
class Pickup implements FulfilmentMethod
{
    public const KEY = 'pickup';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getLabel(): string
    {
        return __('lunar::fulfilment.methods.pickup');
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return [
            Pending::class,
            ReadyForPickup::class,
            PickedUp::class,
            Cancelled::class,
            Returned::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function transitions(): array
    {
        return [
            Pending::class => [ReadyForPickup::class, PickedUp::class, Cancelled::class],
            ReadyForPickup::class => [Pending::class, PickedUp::class, Cancelled::class],
            // A picked-up fulfilment can be reverted to `Pending` (undo the pickup)
            // or marked `Returned` (the customer brings goods back).
            PickedUp::class => [Pending::class, Returned::class],
            Cancelled::class => [],
            Returned::class => [PickedUp::class],
        ];
    }

    public function defaultState(): string
    {
        return Pending::class;
    }

    public function fulfilledState(): string
    {
        return PickedUp::class;
    }

    /**
     * {@inheritDoc}
     */
    public function claim(Order $order, Collection $unclaimed): Collection
    {
        if (! $this->orderPicksUp($order)) {
            return $unclaimed->take(0);
        }

        return $unclaimed->filter(fn ($line) => $line->requires_shipping)->values();
    }

    public function priority(): int
    {
        return 20;
    }

    public function usesTracking(): bool
    {
        return false;
    }

    /**
     * Whether the order's chosen shipping option is a pickup, read from the
     * `pickup` flag stamped onto the shipping line's meta at order creation.
     */
    protected function orderPicksUp(Order $order): bool
    {
        $meta = $order->lines()->where('type', 'shipping')->first()?->meta;

        return (bool) ($meta['pickup'] ?? false);
    }
}
