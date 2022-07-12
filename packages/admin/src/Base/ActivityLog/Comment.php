<?php

namespace GetCandy\Hub\Base\ActivityLog;

use Spatie\Activitylog\Models\Activity;

class Comment extends AbstractRender
{
    public function getEvent(): string
    {
        return 'comment';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.orders.activity.comment', [
            'log' => $log,
        ]);
    }
}
