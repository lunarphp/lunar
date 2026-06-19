<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Models\Fulfilment;
use Spatie\ModelStates\DefaultTransition;

/**
 * The single guard transition every fulfilment transition is registered with.
 *
 * Spatie resolves one transition graph for the whole `FulfilmentState`
 * hierarchy (its `config()` is static and can't see the `method` column), so
 * `FulfilmentState::config()` registers the *union* of every method's
 * transitions — which would otherwise let a `collection` fulfilment move to
 * `Shipped` (the `pending → shipped` key exists, contributed by `shipping`).
 *
 * This guard closes that: `State::transition()` invokes `canTransition()` (with
 * the model injected) before applying the change, and it consults the fulfilment's
 * *own* method graph. Because a union key is global (one transition class per
 * key), one generic guard reading the model's method handles every overlapping
 * key — so `transitionTo()`, `canTransitionTo()` and `transitionableStates()`
 * all enforce the per-method graph inside Spatie.
 */
class MethodAwareTransition extends DefaultTransition
{
    public function canTransition(): bool
    {
        /** @var Fulfilment $fulfilment */
        $fulfilment = $this->model;

        // Still the old state at guard time (the field is set in handle()).
        $from = $fulfilment->state;

        return in_array(
            $this->newState::class,
            $fulfilment->method()->transitions()[$from::class] ?? [],
            true,
        );
    }
}
