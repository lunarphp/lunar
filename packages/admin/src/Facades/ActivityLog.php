<?php

namespace Lunar\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Hub\Base\ActivityLog\Manifest;

class ActivityLog extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Manifest::class;
    }
}
