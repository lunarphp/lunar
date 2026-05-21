<?php

namespace Lunar\Core\Exceptions;

class NonPurchasableItemException extends LunarException
{
    public function __construct(string $classname)
    {
        $this->message = __('lunar::exceptions.non_purchasable_item', [
            'class' => $classname,
        ]);
    }
}
