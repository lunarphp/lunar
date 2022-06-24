<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Actions\ActionRegistry;
use GetCandy\Hub\Base\ActivityLog\Manifest;
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
