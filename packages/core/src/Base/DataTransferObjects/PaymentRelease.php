<?php

namespace GetCandy\Base\DataTransferObjects;

class PaymentRelease
{
    public function __construct(
        public bool $success = false
    ) {
        //
    }
}
