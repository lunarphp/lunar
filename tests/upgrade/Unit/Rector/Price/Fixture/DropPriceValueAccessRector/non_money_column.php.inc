<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

function leave_non_money_alone(\Lunar\Core\Models\Order $order): mixed
{
    return $order->reference->value;
}

?>
