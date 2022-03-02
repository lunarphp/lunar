<?php

namespace GetCandy\Base\DataTransferObjects;

use GetCandy\Models\Price;
use Illuminate\Support\Collection;

class PaymentRelease
{
    public function __construct(
        public bool $success = false
    ) {
        //
    }
}
