<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewritePriceFormattedCallRector;

function formatted(\Lunar\Core\Models\Order $order, \Lunar\Core\Models\Price $price): array
{
    return [
        $order->total->formatted(),
        $order->shipping_total->formatted('en_GB', 4, false),
        $price->price->formatted(),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewritePriceFormattedCallRector;

function formatted(\Lunar\Core\Models\Order $order, \Lunar\Core\Models\Price $price): array
{
    return [
        $order->format('total'),
        $order->format('shipping_total', 'en_GB', 4, false),
        $price->format('price'),
    ];
}

?>
