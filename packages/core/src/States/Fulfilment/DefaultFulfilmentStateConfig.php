<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Contracts\FulfilmentStateConfig;

class DefaultFulfilmentStateConfig implements FulfilmentStateConfig
{
    public function fulfilmentStates(): array
    {
        return [
            Pending::class,
            InProgress::class,
            Shipped::class,
            Cancelled::class,
            Returned::class,
        ];
    }

    public function fulfilmentTransitions(): array
    {
        return [
            Pending::class => [InProgress::class, Shipped::class, Cancelled::class],
            InProgress::class => [Pending::class, Shipped::class, Cancelled::class],
            // A shipped parcel can be reverted to `Pending` (an "un-ship" to
            // correct a mistaken dispatch — the items return to the unfulfilled
            // pool and the parcel becomes re-shippable) or marked `Returned`.
            Shipped::class => [Pending::class, Returned::class],
            Cancelled::class => [],
            // A return can be undone back to `Shipped` (the parcel did ship —
            // only the return was a mistake), restoring the shipment.
            Returned::class => [Shipped::class],
        ];
    }

    public function defaultFulfilmentState(): string
    {
        return Pending::class;
    }

    public function notificationsFor(FulfilmentState $state): array
    {
        /** @var array<class-string> $notifications */
        $notifications = (array) config('lunar.orders.notifications.'.$state::$name, []);

        return $notifications;
    }
}
