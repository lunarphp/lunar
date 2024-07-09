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
     * @return Cart
     */
    public function apply(Cart $cart): Cart
    {
        // ...
        return $cart;
    }
}
```


## Adding form fields for your discount in the admin panel

If you require fields in the Lunar admin for your discount type, ensure your discount implements `Lunar\Admin\Base\LunarPanelDiscountInterface`. You will need to provide the `lunarPanelSchema`, `lunarPanelOnFill` and `lunarPanelOnSave` methods.

```php
<?php

namespace App\DiscountTypes;

use Lunar\Admin\Base\LunarPanelDiscountInterface;
use Lunar\DiscountTypes\AbstractDiscountType;

class MyCustomDiscountType extends AbstractDiscountType implements LunarPanelDiscountInterface
{
    /**
     * Return the schema to use in the Lunar admin panel
     */
    public function lunarPanelSchema(): array
    {
        return [
            Forms\Components\TextInput::make('my_field')
               ->label('My label')
               ->required(),
        ];
    }

    /**
     * Mutate the model data before displaying it in the admin form.
     */
    public function lunarPanelOnFill(array $data): array
    {
        // optionally do something with $data
        return $data;
    }

    /**
     * Mutate the form data before saving it to the discount model.
     */
    public function lunarPanelOnSave(array $data): array
    {
        // optionally do something with $data
        return $data;
    }
}
```
