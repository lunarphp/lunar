<?php

namespace Lunar\Checkout\Drivers;

use Lunar\Checkout\Contracts\CheckoutDriver;

/**
 * Mandatory base class for checkout drivers (spec 0010 §B). The contract-
 * stability anchor: future `CheckoutDriver` verbs land here with default
 * implementations (or explicit `…NotSupported` exceptions), so interface
 * growth never breaks a third-party driver that extends this class.
 */
abstract class AbstractCheckoutDriver implements CheckoutDriver {}
