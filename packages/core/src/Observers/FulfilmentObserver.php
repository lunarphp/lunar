<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Events\Fulfilment\FulfilmentStatusUpdated;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

class FulfilmentObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
    ) {}

    /**
     * Handle the Fulfilment "updating" event — log a state change.
     */
    public function updating(FulfilmentContract $fulfilment): void
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->isDirty('state')) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($fulfilment->order()->first())
                ->event('fulfilment-state-update')
                ->withProperties([
                    'fulfilment_id' => $fulfilment->id,
                    'new' => (string) $fulfilment->state,
                    'previous' => (string) $fulfilment->getOriginal('state'),
                ])
                ->log('fulfilment-state-update');
        }
    }

    /**
     * Handle the Fulfilment "created" event.
     */
    public function created(FulfilmentContract $fulfilment): void
    {
        $this->recompute($fulfilment);
    }

    /**
     * Handle the Fulfilment "updated" event.
     */
    public function updated(FulfilmentContract $fulfilment): void
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->wasChanged('state')) {
            $previous = $fulfilment->getOriginal('state');

            FulfilmentStatusUpdated::dispatch(
                $fulfilment,
                $previous instanceof FulfilmentState ? $previous : null,
                $fulfilment->state,
            );
        }

        $this->recompute($fulfilment);
    }

    /**
     * Handle the Fulfilment "deleted" event.
     */
    public function deleted(FulfilmentContract $fulfilment): void
    {
        $this->recompute($fulfilment);
    }

    /**
     * Recompute the parent order's derived fulfilment status.
     */
    protected function recompute(FulfilmentContract $fulfilment): void
    {
        /** @var Fulfilment $fulfilment */
        if ($order = $fulfilment->order()->first()) {
            $this->recomputeOrderStatus->execute($order);
        }
    }
}
