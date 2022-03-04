<?php

namespace GetCandy\Base\DataTransferObjects;

use GetCandy\Models\Price;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Nullable;

class PaymentRefund
{
    public function __construct(
        public bool $success = false,
        public string $message = ''
    ) {
        //
    }
}
