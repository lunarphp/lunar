<?php

namespace GetCandy\Hub\Base\ActivityLog;

use Spatie\Activitylog\Models\Activity;

abstract class AbstractRender
{
    abstract public function getEvent(): string;
    abstract public function render(Activity $log);
}
