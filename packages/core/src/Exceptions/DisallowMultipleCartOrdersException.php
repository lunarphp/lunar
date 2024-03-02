<?php

namespace Lunar\Exceptions;

class DisallowMultipleCartOrdersException extends LunarException
{
    public function __construct()
    {
        $this->message = __('lunar::exceptions.disallow_multiple_cart_orders');
    }
}
