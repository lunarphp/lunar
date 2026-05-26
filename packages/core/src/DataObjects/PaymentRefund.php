<?php

namespace Lunar\Core\DataObjects;

class PaymentRefund
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null
    ) {
        //
    }
}
