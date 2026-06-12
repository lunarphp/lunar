<?php

namespace Lunar\Checkout\Events;

/**
 * The pay-boundary gate rejected a cart that changed since the customer
 * confirmed (spec 0010 §E).
 */
class CheckoutPaymentConfirmationFailed extends CheckoutSessionEvent {}
