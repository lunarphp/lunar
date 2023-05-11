# Discounts

## Overview

If you want to add additional functionality to Discounts, you can register your own custom discount types.

## Registering a discount type.

```php
use Lunar\Facades\Discounts;

Discounts::addType(MyCustomDiscountType::class);
```


```php
<?php

namespace App\DiscountTypes;

use Lunar\Models\Cart;
use Lunar\DiscountTypes\AbstractDiscountType;

class MyCustomDiscountType extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Custom Discount Type';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        // ...
        return $cart;
    }
}
```
