<?php

namespace Lunar\Core\DataObjects;

/**
 * A structured refund request: the capture transaction to refund against,
 * the order lines/quantities being refunded, and optional shipping and
 * manual-adjustment portions. The refund amount is the sum of all three.
 */
class RefundRequest
{
    /**
     * @param  int|string  $transactionId  The capture transaction to refund against.
     * @param  array<int, array{order_line_id: int, quantity: int}>  $lines  Lines/quantities being refunded.
     * @param  float|int|string  $shipping  Major-unit shipping amount to refund.
     * @param  float|int|string  $adjustment  Major-unit manual adjustment (can be negative); also how an amount-only refund is expressed with empty `$lines`.
     * @param  bool  $notify  Whether the customer receives a refund notification, when one is registered.
     */
    public function __construct(
        public int|string $transactionId,
        public array $lines = [],
        public float|int|string $shipping = 0,
        public float|int|string $adjustment = 0,
        public ?string $notes = null,
        public bool $notify = true,
    ) {}
}
