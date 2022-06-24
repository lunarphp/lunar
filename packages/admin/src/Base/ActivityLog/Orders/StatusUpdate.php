<?php

namespace GetCandy\Hub\Base\ActivityLog\Orders;

use Spatie\Activitylog\Models\Activity;
use GetCandy\Hub\Base\ActivityLog\AbstractRender;

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
