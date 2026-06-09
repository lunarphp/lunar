<?php

namespace Lunar\Checkout\Contracts;

/**
 * Prototype stand-in for the spec 0004 CheckoutSession. The full model snapshots
 * lines, pins channel/currency, carries a state machine and a public uuid. Until
 * that lands, this is a thin per-shopper value store that gives checkout elements
 * a place to read and persist the data they capture, scoped to the active session.
 */
interface CheckoutSession
{
    /** Read a captured value (typically by element handle). */
    public function get(string $key, mixed $default = null): mixed;

    /** Persist a captured value. */
    public function put(string $key, mixed $value): void;

    /** Forget a captured value. */
    public function forget(string $key): void;

    /** Every captured value, keyed. */
    public function all(): array;
}
