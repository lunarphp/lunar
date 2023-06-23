<?php

namespace Lunar\Exceptions;

use Exception;

class DisallowMultipleCartOrdersException extends Exception
{
    public function __construct()
    {
        $this->message = __('lunar::exceptions.disallow_multiple_cart_orders');
    }
}
