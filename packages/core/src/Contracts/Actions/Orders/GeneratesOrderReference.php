<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;

interface GeneratesOrderReference
{
    public function execute(OrderContract $order): ?string;
}
