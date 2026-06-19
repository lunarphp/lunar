<?php

namespace Lunar\Core\Drivers\FulfilmentMethods;

use Illuminate\Support\Collection as SupportCollection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\Collected;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\ReadyForCollection;
use Lunar\Core\States\Fulfilment\Returned;

/**
 * Click-and-collect / trade-counter pickup. Claims physical lines when the
 * order's chosen shipping option is a collection (`collect === true`, persisted
 * onto the shipping line at order creation), so the fulfilment presents "ready for
 * collection → collected" instead of ship-and-track. No tracking.
 */
class Collection implements FulfilmentMethod
{
    public const KEY = 'collection';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getLabel(): string
    {
        return __('lunar::fulfilment.methods.collection');
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return [
            Pending::class,
            ReadyForCollection::class,
            Collected::class,
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
            Pending::class => [ReadyForCollection::class, Collected::class, Cancelled::class],
            ReadyForCollection::class => [Pending::class, Collected::class, Cancelled::class],
            // A collected fulfilment can be reverted to `Pending` (un-collect) or
            // marked `Returned` (the customer brings goods back).
            Collected::class => [Pending::class, Returned::class],
            Cancelled::class => [],
            Returned::class => [Collected::class],
        ];
    }

    public function defaultState(): string
    {
        return Pending::class;
    }

    public function fulfilledState(): string
    {
        return Collected::class;
    }

    /**
     * {@inheritDoc}
     */
    public function claim(Order $order, SupportCollection $unclaimed): SupportCollection
    {
        if (! $this->orderCollects($order)) {
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
     * Whether the order's chosen shipping option is a collection, read from the
     * `collect` flag stamped onto the shipping line's meta at order creation.
     */
    protected function orderCollects(Order $order): bool
    {
        $meta = $order->lines()->where('type', 'shipping')->first()?->meta;

        return (bool) ($meta['collect'] ?? false);
    }
}
