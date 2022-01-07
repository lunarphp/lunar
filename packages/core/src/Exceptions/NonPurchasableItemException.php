<?php

namespace GetCandy\Exceptions;

use Exception;

class NonPurchasableItemException extends Exception
{
    public function __construct(string $classname)
    {
        $this->message = __('getcandy::exceptions.non_purchasable_item', [
            'class' => $classname,
        ]);
    }
}
