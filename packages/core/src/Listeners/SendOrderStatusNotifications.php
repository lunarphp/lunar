<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Events\Orders\OrderStatusUpdated;

class SendOrderStatusNotifications
{
    public function __construct(
        protected OrderStateConfig $stateConfig,
    ) {}

    public function handle(OrderStatusUpdated $event): void
    {
        foreach ($this->stateConfig->notificationsFor($event->newStatus) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
