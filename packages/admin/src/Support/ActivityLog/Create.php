<?php

namespace Lunar\Admin\Support\ActivityLog;

use Spatie\Activitylog\Models\Activity;

class Create extends AbstractRender
{
    public function getEvent(): string
    {
        return 'created';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.activity-log.create', [
            'log' => $log,
            'model' => str($log->subject::class)->classBasename()->snake(' ')->ucfirst(),
        ]);
    }
}
