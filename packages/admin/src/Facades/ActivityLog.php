<?php

namespace Lunar\Hub\Facades;

use Lunar\Hub\Base\ActivityLog\Manifest;
use Illuminate\Support\Facades\Facade;

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
