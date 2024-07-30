<?php

namespace Lunar\Admin\Support\ActivityLog;

use Spatie\Activitylog\Models\Activity;

abstract class AbstractRender
{
    abstract public function getEvent(): string;

    abstract public function render(Activity $log);
}
