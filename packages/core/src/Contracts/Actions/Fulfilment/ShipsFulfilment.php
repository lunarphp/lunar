<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;

interface ShipsFulfilment
{
    /**
     * Transition a fulfilment to shipped, stamping `shipped_at` and recording
     * tracking details. Pass `$notify: false` to suppress the customer
     * notification this state change would otherwise trigger.
     *
     * @param  array<string, mixed>  $tracking  tracking_number / tracking_url / shipping_method
     */
    public function execute(Fulfilment $fulfilment, array $tracking = [], bool $notify = true): Fulfilment;
}
