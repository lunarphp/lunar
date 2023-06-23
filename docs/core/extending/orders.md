# Order Extending

## Overview

If you want to add additional functionality to the Order creation process, you can do so using pipelines.

## Pipelines

### Adding an Order Pipeline

All pipelines are defined in `config/lunar/orders.php`

```php
'pipelines' => [
    'creation' => [
        Lunar\Pipelines\Order\Creation\FillOrderFromCart::class,
        Lunar\Pipelines\Order\Creation\CreateOrderLines::class,
        Lunar\Pipelines\Order\Creation\CreateOrderAddresses::class,
        Lunar\Pipelines\Order\Creation\CreateShippingLine::class,
        Lunar\Pipelines\Order\Creation\CleanUpOrderLines::class,
        Lunar\Pipelines\Order\Creation\MapDiscountBreakdown::class,
        // ...
    ],
],
```

You can add your own pipelines to the configuration, they might look something like:

```php
<?php

namespace App\Pipelines\Orders;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Models\Order;

class CustomOrderPipeline
{
    /**
     * @return void
     */
    public function handle(Order $order, Closure $next)
    {
        // Do something to the cart...

        return $next($order);
    }
}
```

```php
'pipelines' => [
    'creation' => [
        // ...
        App\Pipelines\Orders\CustomOrderPipeline::class,
    ],   
],
```

::: tip
Pipelines will run from top to bottom
:::