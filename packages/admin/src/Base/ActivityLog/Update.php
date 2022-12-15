<?php

namespace Lunar\Hub\Base\ActivityLog;

use Spatie\Activitylog\Models\Activity;

class Update extends AbstractRender
{
    public function getEvent(): string
    {
        return 'updated';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.activity-log.update', [
            'log' => $log,
        ]);
    }
}
