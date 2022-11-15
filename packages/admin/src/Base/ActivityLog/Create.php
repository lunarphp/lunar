<?php

namespace Lunar\Hub\Base\ActivityLog;

use Spatie\Activitylog\Models\Activity;

class Create extends AbstractRender
{
    public function getEvent(): string
    {
        return 'created';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.activity-log.create', [
            'log' => $log,
        ]);
    }
}
