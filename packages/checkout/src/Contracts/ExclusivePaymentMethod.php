<?php

namespace Lunar\Checkout\Contracts;

/**
 * Marker for a payment method that, when available for a cart, is the only
 * method offered (spec 0014 §B). A trade customer paying on account does not
 * also get a card form. Resolved in one place, PaymentMethodRegistry::availableFor(),
 * which already feeds the payment step, the express wallets and the pay boundary.
 */
interface ExclusivePaymentMethod {}
