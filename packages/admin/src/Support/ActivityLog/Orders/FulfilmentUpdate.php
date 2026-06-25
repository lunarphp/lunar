<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class FulfilmentUpdate extends AbstractRender
{
    public function getEvent(): string
    {
        return 'fulfilment-update';
    }

    public function render(Activity $log)
    {
        return view('lunarpanel::partials.orders.activity.fulfilment-update', [
            'log' => $log,
        ]);
    }
}
