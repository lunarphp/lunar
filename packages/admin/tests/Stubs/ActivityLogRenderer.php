<?php

namespace Lunar\Hub\Tests\Stubs;

use Lunar\Hub\Base\ActivityLog\AbstractRender;
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
