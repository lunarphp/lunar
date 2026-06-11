<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\States\CheckoutSession\CheckoutSessionState;

/**
 * Catalogue + transition table for the checkout-session lifecycle machine.
 *
 * The single seam for reshaping the machine — bind your implementation during
 * service-provider `register()` (Octane-safe; Spatie caches the resolved state
 * mapping per class for the process lifetime, so runtime rebinds are invisible
 * to already-cached machines). Mirrors `OrderStateConfig`.
 */
interface CheckoutSessionStateConfig
{
    /**
     * @return array<class-string<CheckoutSessionState>>
     */
    public function states(): array;

    /**
     * @return array<class-string<CheckoutSessionState>, list<class-string<CheckoutSessionState>>>
     */
    public function transitions(): array;

    /**
     * @return class-string<CheckoutSessionState>
     */
    public function defaultState(): string;
}
