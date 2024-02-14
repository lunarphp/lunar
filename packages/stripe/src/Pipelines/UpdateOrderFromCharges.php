<?php

namespace Lunar\Stripe\Pipelines;

use Lunar\Stripe\DataTransferObjects\OrderIntent;

class UpdateOrderFromCharges
{
    public function handle(OrderIntent $orderIntent, \Closure $next)
    {

    }
}
