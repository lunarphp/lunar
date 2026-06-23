<?php

namespace Lunar\Checkout\Drivers;

use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * Mandatory base class for checkout drivers (spec 0010 §B). The contract-
 * stability anchor: future `CheckoutDriver` verbs land here with default
 * implementations (or explicit `…NotSupported` exceptions), so interface
 * growth never breaks a third-party driver that extends this class.
 */
abstract class AbstractCheckoutDriver implements CheckoutDriver
{
    /**
     * Safe fallback: drivers that can cheaply locate an existing session
     * SHOULD override this with a real resolve-or-create. The default always
     * creates (and therefore supersedes per the create rules).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function resolveOrCreateSession(mixed $source, array $attributes = []): CheckoutSession
    {
        return $this->createSession($source, $attributes);
    }
}
