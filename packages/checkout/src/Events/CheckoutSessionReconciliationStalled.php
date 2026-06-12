<?php

namespace Lunar\Checkout\Events;

/**
 * Bounded reconciliation exhausted its attempts on a `PaymentProcessing`
 * session (spec 0010 §F). Fired once; the session drops to the low-frequency
 * retry tier until an operator resolves it.
 */
class CheckoutSessionReconciliationStalled extends CheckoutSessionEvent {}
