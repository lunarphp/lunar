<?php

namespace Lunar\Checkout\Contracts;

interface ElementRegistry
{
    /**
     * Register a checkout element by class name or instance. Registration is a
     * build-time concern (Octane-safe): the resolved set is read per request,
     * never mutated at request time.
     *
     * @param  class-string<CheckoutElement>|CheckoutElement  $element
     */
    public function add(string|CheckoutElement $element): static;

    /**
     * Resolved element instances, in registration order.
     *
     * @return array<int, CheckoutElement>
     */
    public function all(): array;

    /** Resolve a single registered element by handle, or null. */
    public function get(string $handle): ?CheckoutElement;
}
