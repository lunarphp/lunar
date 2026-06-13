<?php

namespace Lunar\Core\Drivers\FulfilmentMethods;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\InProgress;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Returned;
use Lunar\Core\States\Fulfilment\Shipped;

/**
 * The post-a-parcel-through-a-carrier flow — today's behaviour (specs 0022 /
 * 0024) expressed as a registered method. The catch-all: it claims every
 * remaining physical line, so an all-physical order gets exactly one parcel as
 * before. The only core method that carries tracking.
 *
 * Not to be confused with table-rate-shipping's `ShippingMethod` Eloquent model
 * — this is a fulfilment-flow driver, hence the `FulfilmentMethods` namespace.
 */
class Shipping implements FulfilmentMethod
{
    public const KEY = 'shipping';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getLabel(): string
    {
        return __('lunar::fulfilment.methods.shipping');
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return [
            Pending::class,
            InProgress::class,
            Shipped::class,
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
            Pending::class => [InProgress::class, Shipped::class, Cancelled::class],
            InProgress::class => [Pending::class, Shipped::class, Cancelled::class],
            // A shipped parcel can be reverted to `Pending` (un-ship) or
            // marked `Returned`.
            Shipped::class => [Pending::class, Returned::class],
            Cancelled::class => [],
            // A return can be undone back to `Shipped`.
            Returned::class => [Shipped::class],
        ];
    }

    public function defaultState(): string
    {
        return Pending::class;
    }

    public function fulfilledState(): string
    {
        return Shipped::class;
    }

    /**
     * {@inheritDoc}
     */
    public function claim(Order $order, Collection $unclaimed): Collection
    {
        return $unclaimed->filter(fn ($line) => $line->requires_shipping)->values();
    }

    public function priority(): int
    {
        return 30;
    }

    public function usesTracking(): bool
    {
        return true;
    }
}
