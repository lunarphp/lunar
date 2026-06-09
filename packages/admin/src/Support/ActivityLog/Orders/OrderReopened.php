<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class OrderReopened extends AbstractRender
{
    public function getEvent(): string
    {
        return 'order-reopened';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.order-lifecycle', [
            'log' => $log,
        ]);
    }
}
