<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Actions\Fixture\RewriteActionRunCallRector;

use Lunar\Core\Actions\Carts\CreateOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;

function run(\Lunar\Core\Models\Cart $cart, \Lunar\Core\Models\Order $order): array
{
    return [
        CreateOrder::run($cart),
        CaptureOrder::run($order),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Actions\Fixture\RewriteActionRunCallRector;

use Lunar\Core\Actions\Carts\CreateOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;

function run(\Lunar\Core\Models\Cart $cart, \Lunar\Core\Models\Order $order): array
{
    return [
        app(\Lunar\Core\Contracts\Actions\Carts\CreatesOrder::class)->execute($cart),
        app(\Lunar\Core\Contracts\Actions\Orders\CapturesOrder::class)->execute($order),
    ];
}

?>
