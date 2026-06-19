<?php

namespace Lunar\Core\Contracts\Notifications;

/**
 * Marks a customer notification that accepts an optional ad-hoc message from
 * the NotifyCustomer action. The contract documents the constructor shape the
 * action relies on:
 *
 *     public function __construct(Order $order, ?string $message = null)
 *
 * NotifyCustomer instantiates every variant as `new $class($order, $message)`;
 * a notification that does not implement this contract simply ignores the
 * second argument. Implement it to advertise (and type-check) that the message
 * is rendered into the email.
 */
interface AcceptsCustomerMessage {}
