<?php

namespace GetCandy\Hub\Tests\Stubs;

use GetCandy\Hub\Base\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRenderer extends AbstractRender
{
    public function getEvent(): string
    {
        return 'created';
    }

    public function render(Activity $log)
    {
        return '';
    }
}
