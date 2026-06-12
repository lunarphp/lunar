<?php

namespace Lunar\Checkout\DataObjects;

use Spatie\LaravelData\Data;

class CartSnapshot extends Data
{
    public function __construct(
        public int $amountSubtotal,
        public int $amountTotal,
        public string $currencyCode,
        public string $channelHandle,
        public string $fingerprint,
        public bool $hasAppliedDiscount,
        public ?string $couponCode,
    ) {}
}
