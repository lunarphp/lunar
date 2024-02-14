<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;

class StripeFacade extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'gc:stripe';
    }
}
