<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class Capture extends AbstractRender
{
    public function getEvent(): string
    {
        return 'capture';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.capture', [
            'log' => $log,
        ]);
    }
}
