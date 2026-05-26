<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewritePriceDecimalCallRector;

function decimals(\Lunar\Core\Models\Order $order, \Lunar\Core\Models\Transaction $transaction): array
{
    return [
        $order->total->decimal(),
        $order->shipping_total->decimal(false),
        $transaction->amount->decimal(),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewritePriceDecimalCallRector;

function decimals(\Lunar\Core\Models\Order $order, \Lunar\Core\Models\Transaction $transaction): array
{
    return [
        $order->decimal('total'),
        $order->decimal('shipping_total', false),
        $transaction->decimal('amount'),
    ];
}

?>
