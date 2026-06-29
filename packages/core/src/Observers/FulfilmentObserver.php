<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Events\Fulfilment\FulfilmentStatusUpdated;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

class FulfilmentObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
    ) {}

    /**
     * Handle the Fulfilment "updating" event — log meaningful fulfilment
     * changes (state transitions, holds) against the parent order so they read
     * clearly in the timeline.
     */
    public function updating(Fulfilment $fulfilment): void
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->isDirty('state')) {
            $this->logFulfilmentUpdate($fulfilment, [
                'type' => 'state',
                'from' => (string) $fulfilment->getOriginal('state'),
                'to' => (string) $fulfilment->state,
            ]);
        }

        if ($fulfilment->isDirty('held_at')) {
            $this->logFulfilmentUpdate($fulfilment, $fulfilment->held_at ? [
                'type' => 'held',
                'reason' => $fulfilment->hold_reason,
            ] : [
                'type' => 'released',
            ]);
        }
    }

    /**
     * Record a fulfilment change against its order's activity timeline.
     *
     * @param  array<string, mixed>  $properties
     */
    protected function logFulfilmentUpdate(Fulfilment $fulfilment, array $properties): void
    {
        /** @var Fulfilment $fulfilment */
        if (! $order = $fulfilment->order()->first()) {
            return;
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->event('fulfilment-update')
            ->withProperties(array_merge([
                'fulfilment_id' => $fulfilment->id,
                'reference' => $fulfilment->reference,
            ], $properties))
            ->log('fulfilment-update');
    }

    /**
     * Handle the Fulfilment "created" event.
     */
    public function created(Fulfilment $fulfilment): void
    {
        $this->recompute($fulfilment);
    }

    /**
     * Handle the Fulfilment "updated" event.
     */
    public function updated(Fulfilment $fulfilment): void
    {
        /** @var Fulfilment $fulfilment */
        // The verb that drove this state change stamped its "notify the
        // customer" intent on the instance; carry it onto the (queue-safe)
        // event and into the order-status recompute.
        $notify = $fulfilment->notifyOnStatusChange;

        if ($fulfilment->wasChanged('state')) {
            $previous = $fulfilment->getOriginal('state');

            FulfilmentStatusUpdated::dispatch(
                $fulfilment,
                $previous instanceof FulfilmentState ? $previous : null,
                $fulfilment->state,
                $notify,
            );
        }

        $this->recompute($fulfilment, $notify);
    }

    /**
     * Handle the Fulfilment "deleted" event.
     */
    public function deleted(Fulfilment $fulfilment): void
    {
        $this->recompute($fulfilment);
    }

    /**
     * Recompute the parent order's derived fulfilment status, carrying the
     * "notify the customer" intent of the change that triggered it.
     */
    protected function recompute(Fulfilment $fulfilment, bool $notify = true): void
    {
        /** @var Fulfilment $fulfilment */
        if ($order = $fulfilment->order()->first()) {
            $this->recomputeOrderStatus->execute($order, $notify);
        }
    }
}
