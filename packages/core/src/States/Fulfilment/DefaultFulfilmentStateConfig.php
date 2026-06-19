<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Contracts\FulfilmentStateConfig;

/**
 * Derives the per-fulfilment state catalogue from the registered fulfilment
 * methods. Spatie sees the *union* of every method's states and transitions —
 * not only for breadth, but because Spatie auto-discovers state classes by
 * scanning the base class's own directory, so a consumer's states in their own
 * namespace are invisible unless the manifest union registers them. The
 * per-method graph is then enforced at runtime by {@see MethodAwareTransition}.
 *
 * Replacing this contract wholesale still swaps the entire catalogue; reshaping
 * a *single* flow is "register/replace a FulfilmentMethod" instead.
 */
class DefaultFulfilmentStateConfig implements FulfilmentStateConfig
{
    public function __construct(
        protected FulfilmentMethodManifest $methods,
    ) {}

    public function fulfilmentStates(): array
    {
        return $this->methods->states();
    }

    public function fulfilmentTransitions(): array
    {
        return $this->methods->transitions();
    }

    public function defaultFulfilmentState(): string
    {
        // The global fallback for the unblessed path; CreateFulfilment stamps
        // the method's own defaultState() explicitly.
        return Pending::class;
    }

    public function notificationsFor(FulfilmentState $state): array
    {
        /** @var array<class-string> $notifications */
        $notifications = (array) config('lunar.orders.notifications.'.$state::$name, []);

        return $notifications;
    }
}
