<?php

namespace Lunar\Validation;

use Illuminate\Support\MessageBag;
use Lunar\Exceptions\Carts\CartException;

class BaseValidator
{
    public function fail($where, $reason)
    {
        $messages = new MessageBag([
            $where => $reason,
        ]);

        throw_if(
            $messages->isNotEmpty(),
            CartException::class,
            $messages
        );
    }
}
