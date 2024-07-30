<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class Intent extends AbstractRender
{
    public function getEvent(): string
    {
        return 'intent';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.intent', [
            'log' => $log,
        ]);
    }
}
