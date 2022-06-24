<?php

namespace GetCandy\Hub\Base\ActivityLog\Orders;

use GetCandy\Hub\Base\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class Capture extends AbstractRender
{
    public function getEvent(): string
    {
        return 'capture';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.orders.activity.capture', [
            'log' => $log,
        ]);
    }
}
