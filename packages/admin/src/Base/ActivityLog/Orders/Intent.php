<?php

namespace GetCandy\Hub\Base\ActivityLog\Orders;

use Spatie\Activitylog\Models\Activity;
use GetCandy\Hub\Base\ActivityLog\AbstractRender;

class Intent extends AbstractRender
{
    public function getEvent(): string
    {
        return 'intent';
    }

    public function render(Activity $log)
    {
        return view('adminhub::partials.orders.activity.intent', [
            'log' => $log,
        ]);
    }
}
