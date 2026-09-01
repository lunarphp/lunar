<?php

namespace Lunar\Checkout\Contracts;

/**
 * Key/value store for the data checkout elements capture, keyed by element
 * handle (the spec 0010 §C element bag).
 *
 * Deliberately NOT called CheckoutSession: that name means the persisted row
 * (Models\CheckoutSession), and having two unrelated objects share it made
 * "is $session the row or the bag?" a coin flip at every call site.
 */
interface ElementDataStore
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
