<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Admin\Support\ActivityLog\Manifest;

/**
 * @method static Manifest addRender(string $subject, string $renderer)
 * @method static Collection getItems(string $subject)
 *
 * @see Manifest
 */
class ActivityLog extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lunar-activity-log';
    }
}
