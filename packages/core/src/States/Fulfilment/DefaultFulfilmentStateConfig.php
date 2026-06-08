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
            Shipped::class => [Returned::class],
            Cancelled::class => [],
            Returned::class => [],
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
