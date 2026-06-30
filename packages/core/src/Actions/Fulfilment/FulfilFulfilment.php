<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\FulfilsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\TransitionsFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Fulfilment;

/**
 * Advance a fulfilment to its method's canonical "done" state with no tracking
 * — the generic terminal verb (collection → `Collected`, digital →
 * `Provisioned`, a custom flow → its terminal). `ShipFulfilment` is the
 * tracking-bearing specialisation. The transition routes through
 * `TransitionFulfilment`, so the per-method graph is enforced and the
 * handed-over timestamp is stamped by category.
 */
class FulfilFulfilment implements FulfilsFulfilment
{
    public function __construct(
        protected TransitionsFulfilment $transitionFulfilment,
    ) {}

    public function execute(Fulfilment $fulfilment, bool $notify = true): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->isOnHold()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_on_hold'));
        }

        return $this->transitionFulfilment->execute($fulfilment, $fulfilment->method()->fulfilledState(), $notify);
    }

    /**
     * Whether the fulfilment can be fulfilled, per its method's state graph.
     */
    public static function canRun(Fulfilment $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return ! $fulfilment->isOnHold()
            && $fulfilment->state->canTransitionTo($fulfilment->method()->fulfilledState());
    }
}
