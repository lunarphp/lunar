<?php

namespace GetCandy\Hub\Base\ActivityLog\Orders;

use GetCandy\Hub\Base\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class StatusUpdate extends AbstractRender
{
    public function getEvent(): string
    {
        return 'status-update';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.orders.activity.status-update', [
            'log' => $log,
        ]);
    }
}
