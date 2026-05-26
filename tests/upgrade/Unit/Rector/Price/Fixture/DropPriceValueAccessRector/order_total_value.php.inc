<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

function dump_order_total(\Lunar\Core\Models\Order $order): int
{
    return $order->total->value;
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

function dump_order_total(\Lunar\Core\Models\Order $order): int
{
    return $order->total;
}

?>
