# Discounts

## Overview

// ...

## Discounts

```php
Lunar\Models\Discount
```

|Field|Description|Example|
|:-|:-|:-|
|`id`|||
|`name`|The given name for the discount||
|`handle`|The unique handle for the discount||
|`type`|The type of discount|`Lunar\DiscountTypes\Coupon`|
|`data`|JSON|Any data to be used by the type class
|`starts_at`|The datetime the discount starts (required)|
|`ends_at`|The datetime the discount expires, if `NULL` it won't expire|
|`uses`|How many uses the discount has had|
|`max_uses`|The maximum times this discount can be applied storewide|
|`priority`|The order of priority|
|`stop`|Whether this discount will stop others after propagating|
|`created_at`|||
|`updated_at`|||

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

## Discount Purchasable

You can relate a purchasable to a discount via this model. Each has a type for whether it's a `condition` or `reward`.

- `condition` - If your discount requires these purchasable models to be in the cart to activate
- `reward` - Once the conditions are met, discount one of more of these purchasable models.

```php
Lunar\Models\DiscountPurchasable
```

|Field|Description|Example|
|:-|:-|:-|
|`id`|||
|`discount_id`|||
|`purchasable_type`||`Lunar\Models\ProductVariant`
|`type`|`condition` or `reward`|
|`created_at`|||
|`updated_at`|||

### Relationships

- Purchasables `discount_purchasables`
- Users - `customer_user`

### Adding your own Discount type


```php
namespace App\Discounts;

use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\DataTypes\Price;
use Lunar\Facades\Discounts;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;

class CustomDiscount
{
    protected Discount $discount;

    /**
     * Set the data for the discount to user.
     *
     * @param  array  $data
     * @return self
     */
    public function with(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Custom Discount';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function execute(CartLine $cartLine): CartLine
    {
        $data = $this->discount->data;

        // Return the unaltered cart line back
        if (! $conditionIsMet) {
            return $cartLine;
        }

        $cartLine->discount = $this->discount;
        
        $discountTotal = $cartLine->unitPrice->value * $discountQuantity;

        $cartLine->discountTotal = new Price(
            $discountTotal,
            $cartLine->cart->currency,
            1
        );

        $cartLine->subTotalDiscounted = new Price(
            $line->subTotal->value - $discountTotal,
            $cart->currency,
            1
        );
    }
}

```

```php
Discounts::addType(
    CustomDiscount::class
);
```

