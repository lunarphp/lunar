<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentTracking;

interface AddsFulfilmentTracking
{
    /**
     * Add a tracking reference to a fulfilment.
     *
     * @param  array<string, mixed>  $attributes  tracking_number / tracking_url / shipping_method
     */
    public function execute(Fulfilment $fulfilment, array $attributes): FulfilmentTracking;
}
