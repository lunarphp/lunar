<?php

namespace Lunar\Base;

use Lunar\Models\OrderLine;

interface OrderLineCapabilitiesInterface
{
    public function isRefundable(OrderLine $orderLine): bool;

    public function isCancellable(OrderLine $orderLine): bool;

    public function requiresPhysicalReturn(OrderLine $orderLine): bool;

    public function createsEntitlement(OrderLine $orderLine): bool;

    public function supportsEndOfTerm(OrderLine $orderLine): bool;

    public function allowsAccountCredit(OrderLine $orderLine): bool;
}
