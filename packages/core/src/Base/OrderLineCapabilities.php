<?php

namespace Lunar\Base;

use Lunar\Models\OrderLine;

class OrderLineCapabilities implements OrderLineCapabilitiesInterface
{
    public function isRefundable(OrderLine $orderLine): bool
    {
        return $orderLine->order->isPlaced()
            && in_array($orderLine->type, ['physical', 'digital'], true)
            && $orderLine->total->value > 0;
    }

    public function isCancellable(OrderLine $orderLine): bool
    {
        return $orderLine->order->isPlaced()
            && $orderLine->type === 'digital'
            && $orderLine->total->value > 0;
    }

    public function requiresPhysicalReturn(OrderLine $orderLine): bool
    {
        return $orderLine->order->isPlaced() && $orderLine->type === 'physical';
    }

    public function createsEntitlement(OrderLine $orderLine): bool
    {
        return $orderLine->type === 'digital';
    }

    public function supportsEndOfTerm(OrderLine $orderLine): bool
    {
        return false;
    }

    public function allowsAccountCredit(OrderLine $orderLine): bool
    {
        return $orderLine->order->isPlaced()
            && in_array($orderLine->type, ['physical', 'digital'], true)
            && $orderLine->total->value > 0;
    }
}
