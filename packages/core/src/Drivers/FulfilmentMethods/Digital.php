<?php

namespace Lunar\Core\Drivers\FulfilmentMethods;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Provisioned;

/**
 * Manual digital provisioning — a licence key, an access grant, a voucher. The
 * good is not shippable (`requires_shipping = false`) but does need a human to
 * provision it (`requires_fulfilment = true`), so it gets a parcel that runs
 * `Pending → Provisioned`. No return, no tracking.
 */
class Digital implements FulfilmentMethod
{
    public const KEY = 'digital';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getLabel(): string
    {
        return __('lunar::fulfilment.methods.digital');
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return [
            Pending::class,
            Provisioned::class,
            Cancelled::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function transitions(): array
    {
        return [
            Pending::class => [Provisioned::class, Cancelled::class],
            // A provisioned parcel can be reverted to `Pending` (un-provision).
            Provisioned::class => [Pending::class],
            Cancelled::class => [],
        ];
    }

    public function defaultState(): string
    {
        return Pending::class;
    }

    public function fulfilledState(): string
    {
        return Provisioned::class;
    }

    /**
     * {@inheritDoc}
     */
    public function claim(Order $order, Collection $unclaimed): Collection
    {
        return $unclaimed
            ->filter(fn ($line) => $line->requires_fulfilment && ! $line->requires_shipping)
            ->values();
    }

    public function priority(): int
    {
        return 10;
    }

    public function usesTracking(): bool
    {
        return false;
    }
}
