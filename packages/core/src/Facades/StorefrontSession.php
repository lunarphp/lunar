<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\StorefrontSessionInterface;

/**
 * @see \Lunar\Managers\StorefrontSessionManager
 */
class StorefrontSession extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return StorefrontSessionInterface::class;
    }
}
