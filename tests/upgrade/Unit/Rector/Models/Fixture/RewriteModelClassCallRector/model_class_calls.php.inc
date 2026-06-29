<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Models\Fixture\RewriteModelClassCallRector;

use Lunar\Core\Models\Product;

function relations(): array
{
    return [
        Product::modelClass(),
        \Lunar\Core\Models\Order::modelClass(),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Models\Fixture\RewriteModelClassCallRector;

use Lunar\Core\Models\Product;

function relations(): array
{
    return [
        Product::class,
        \Lunar\Core\Models\Order::class,
    ];
}

?>
