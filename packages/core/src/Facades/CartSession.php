<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\CartSessionInterface;

/**
 * @see \Lunar\Managers\CartSessionManager
 */
class CartSession extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return CartSessionInterface::class;
    }
}
