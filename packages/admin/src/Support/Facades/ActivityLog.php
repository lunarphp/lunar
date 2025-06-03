<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lunar\Admin\Support\ActivityLog\Manifest addRender(string $subject, string $renderer)
 * @method static \Illuminate\Support\Collection getItems(string $subject)
 *
 * @see \Lunar\Admin\Support\ActivityLog\Manifest
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
