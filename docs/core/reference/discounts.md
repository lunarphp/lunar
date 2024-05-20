# Discounts

## Overview

// ...

## Discounts

```php
Lunar\Models\Discount
```

| Field        | Description                                                  | Example                               |
|:-------------|:-------------------------------------------------------------|:--------------------------------------|
| `id`         |                                                              |                                       |
| `name`       | The given name for the discount                              |                                       |
| `handle`     | The unique handle for the discount                           |                                       |
| `type`       | The type of discount                                         | `Lunar\DiscountTypes\BuyXGetY`          |
| `data`       | JSON                                                         | Any data to be used by the type class 
| `starts_at`  | The datetime the discount starts (required)                  |
| `ends_at`    | The datetime the discount expires, if `NULL` it won't expire |
| `uses`       | How many uses the discount has had                           |
| `max_uses`   | The maximum times this discount can be applied storewide     |
| `priority`   | The order of priority                                        |
| `stop`       | Whether this discount will stop others after propagating     |
| `created_at` |                                                              |                                       |
| `updated_at` |                                                              |                                       |

### Creating a discount

```php
Lunar\Models\Discount::create([
    'name' => '20% Coupon',
    'handle' => '20_coupon',
    'type' => 'Lunar\DiscountTypes\Coupon',
    'data' => [
        'coupon' => '20OFF',
        'min_prices' => [
            'USD' => 2000 // $20
        ],
    ],
    'starts_at' => '2022-06-17 13:30:55',
    'ends_at' => null,
    'max_uses' => null,
])
```

### Fetching a discount

The following scopes are available:

```php
/**
* Query for discounts using the `start_at` and `end_at` dates.
 */
Discount::active()->get();

/**
* Query for discounts where the `uses` column is less than the `max_uses` column or `max_uses` is null.
 */
Discount::usable()->get();

/**
* Query for discounts where the associated products are of a certain type, based on given product ids.
 */
Discount::products($productIds, $type = 'condition');
```

### Resetting the discount cache

For performance reasons the applicable discounts are cached per request. If you need to reset this cache (for example after adding a discount code) you should call `resetDiscounts()`:

```php
Discount::resetDiscounts();
```

## Discount Purchasable

You can relate a purchasable to a discount via this model. Each has a type for whether it's a `condition` or `reward`.

- `condition` - If your discount requires these purchasable models to be in the cart to activate
- `reward` - Once the conditions are met, discount one of more of these purchasable models.

```php
Lunar\Models\DiscountPurchasable
```

| Field              | Description             | Example                       |
|:-------------------|:------------------------|:------------------------------|
| `id`               |                         |                               |
| `discount_id`      |                         |                               |
| `purchasable_type` |                         | `Lunar\Models\ProductVariant` 
| `type`             | `condition` or `reward` |
| `created_at`       |                         |                               |
| `updated_at`       |                         |                               |

### Relationships

- Purchasables `discount_purchasables`
- Users - `customer_user`

### Adding your own Discount type

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
     * @return Cart
     */
    public function apply(Cart $cart): Cart
    {
        // ...
        return $cart;
    }
}
```

```php
use Lunar\Facades\Discounts;

Discounts::addType(MyCustomDiscountType::class);
```

