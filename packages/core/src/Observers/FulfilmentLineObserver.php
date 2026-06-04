<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Models\Contracts\FulfilmentLine as FulfilmentLineContract;
use Lunar\Core\Models\FulfilmentLine;

class FulfilmentLineObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
    ) {}

    public function saved(FulfilmentLineContract $fulfilmentLine): void
    {
        $this->recompute($fulfilmentLine);
    }

    public function deleted(FulfilmentLineContract $fulfilmentLine): void
    {
        $this->recompute($fulfilmentLine);
    }

    /**
     * Recompute the parent order's derived fulfilment status.
     */
    protected function recompute(FulfilmentLineContract $fulfilmentLine): void
    {
        /** @var FulfilmentLine $fulfilmentLine */
        if ($order = $fulfilmentLine->fulfilment?->order) {
            $this->recomputeOrderStatus->execute($order);
        }
    }
}
