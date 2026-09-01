<?php

namespace Lunar\Checkout\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * An element persisted its captured data (spec 0001 §I).
 *
 * @property array<string, mixed> $data
 */
class CheckoutElementStored
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public CheckoutSession $session,
        public string $handle,
        public array $data,
    ) {}
}
