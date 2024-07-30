<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class EmailNotification extends AbstractRender
{
    public function getEvent(): string
    {
        return 'email-notification';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.email-notification', [
            'log' => $log,
        ]);
    }
}
