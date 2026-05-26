<?php

namespace Lunar\Core\DataObjects;

class PaymentCheck
{
    public function __construct(
        public bool $successful,
        public string $label,
        public string $message
    ) {}
}
