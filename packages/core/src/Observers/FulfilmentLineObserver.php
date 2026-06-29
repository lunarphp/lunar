<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Models\FulfilmentLine;

class FulfilmentLineObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
    ) {}

    public function saved(FulfilmentLine $fulfilmentLine): void
    {
        $this->recompute($fulfilmentLine);
    }

    public function deleted(FulfilmentLine $fulfilmentLine): void
    {
        $this->recompute($fulfilmentLine);
    }

    /**
     * Recompute the parent order's derived fulfilment status.
     */
    protected function recompute(FulfilmentLine $fulfilmentLine): void
    {
        /** @var FulfilmentLine $fulfilmentLine */
        // Load explicitly (relation method, not lazy property access) so this
        // is safe under Model::preventLazyLoading().
        $order = $fulfilmentLine->fulfilment()->with('order')->first()?->order;

        if ($order) {
            $this->recomputeOrderStatus->execute($order);
        }
    }
}
