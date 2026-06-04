<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\OrderState;

class OrderObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
        protected OrderStateConfig $config,
    ) {}

    public function updating(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->isDirty('status')) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('status-update')
                ->withProperties([
                    'new' => (string) $order->status,
                    'previous' => (string) $order->getOriginal('status'),
                ])
                ->log('status-update');
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */
        if (! $order->wasChanged('status')) {
            return;
        }

        $previous = $order->getOriginal('status');

        OrderStatusUpdated::dispatch(
            $order,
            $previous,
            $order->status,
        );

        // When the merchant transitions out of a manual override, re-derive
        // the headline from the rollups rather than trusting the literal
        // target they picked.
        if ($this->leftOverride($previous, $order->status)) {
            $this->recomputeOrderStatus->execute($order);
        }
    }

    /**
     * Whether the status moved from an override state to a non-override state.
     */
    protected function leftOverride(?OrderState $previous, OrderState $current): bool
    {
        if ($previous === null) {
            return false;
        }

        $overrides = $this->config->overrideStates();

        return in_array($previous::class, $overrides, true)
            && ! in_array($current::class, $overrides, true);
    }
}
