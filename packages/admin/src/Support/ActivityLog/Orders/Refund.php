<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class Refund extends AbstractRender
{
    public function getEvent(): string
    {
        return 'refund';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.refund', [
            'log' => $log,
        ]);
    }
}
