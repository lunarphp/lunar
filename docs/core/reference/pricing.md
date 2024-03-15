# Pricing

## Overview

When you display prices on your storefront, you want to be sure the customer is seeing the correct format relative to
the currency they are purchasing in.

Every storefront is different. We understand as a developer you might want to do this your own way or have very specific
requirements, so we have made price formatting easy to swap out with your own implementation, but also we provide a
suitable default that will suit most use cases.

## Price formatting

The class which handles price formatting is referenced in the `config/pricing.php` file:

```php
return [
    // ...
    'formatter' => \Lunar\Pricing\DefaultPriceFormatter::class,
];
```

When you retrieve a `Lunar\Models\Price` model, you will have access to the `->price` attribute which will return
a `Lunar\DataTypes\Price` object. This is what we will use to get our formatted values.

The `Lunar\DataTypes\Price` class is not limited to database columns and can be found throughout the Lunar core when
dealing with prices, other examples include:

### `Lunar\Models\Order`

- `subTotal`
- `total`
- `taxTotal`
- `discount_total`
- `shipping_total`

### `Lunar\Models\OrderLine`

- `unit_price`
- `sub_total`
- `tax_total`
- `discount_total`
- `total`

### `Lunar\Models\Transaction`

- `amount`

### `DefaultPriceFormatter`

The default price formatter ships with Lunar and will handle most use cases for formatting a price, lets go through
them, first we'll create a standard price model.

```php
$priceModel = \Lunar\Models\Price::create([
    // ...
    'price' => 1000, // Price is an int and should be in the lowest common denominator
    'min_quantity' => 1,
]);

// Lunar\DataTypes\Price
$priceDataType = $priceModel->price;
```

Return the raw value, as it's stored in the database.

```php
echo $priceDataType->value; // 1000
```

Return the decimal representation for the price.The decimal value takes into account how many decimal places you have
set for the currency. So in this example if the
decimal places was 3 you would get 10.000

```php
echo $priceDataType->decimal(rounding: true); // 10.00
echo $priceDataType->unitDecimal(rounding: true); // 10.00
```

You may have noticed these two values are the same, so what's happening? Well the unit decimal will take into account
the unit quantity of the purchasable we have the price for. Let's show another example:

```php
$productVariant = \Lunar\Models\ProductVariant::create([
    // ...
    'unit_quantity' => 10,
]);
```

By setting `unit_quantity` to 10 we're telling Lunar that 10 individual units make up this product at this price point,
this is useful if you're selling something that by itself would be under 1 cent i.e. 0.001EUR, which isn't a valid
price.

```php
$priceModel = $productVariant->prices()->create([
    'price' => 10, // 0.10 EUR
]);

// Lunar\DataTypes\Price
$priceDataType = $priceModel->price;
```

Now lets try again:

```php
echo $priceDataType->decimal(rounding: true); // 0.10
echo $priceDataType->unitDecimal(rounding: true); // 0.01
```

You can see the `unitDecimal` method has taken into account that `10` units make up the price so this gives a unit cost
of `0.01`.

##### Formatting to a currency string

The formatted price uses the native PHP [NumberFormatter](https://www.php.net/manual/en/class.numberformatter.php). If
you wish to specify a locale or formatting style you can, see the examples below.

```php
$priceDataType->price->formatted('fr') // 10,00 £GB
$priceDataType->price->formatted('en-gb', \NumberFormatter::SPELLOUT) // ten point zero zero.
$priceDataType->price->formattedUnit('en-gb') // £10.00
```

## Full reference for `DefaultPriceFormatter`

```php
$priceDataType->decimal(
    rounding: false
);

$priceDataType->decimalUnit(
    rounding: false
);

$priceDataType->formatted(
    locale: null, 
    formatterStyle: NumberFormatter::CURRENCY,
    decimalPlaces: null, 
    trimTrailingZeros: true
);

$priceDataType->unitFormatted(
    locale: null, 
    formatterStyle: NumberFormatter::CURRENCY,
    decimalPlaces: null, 
    trimTrailingZeros: true
);
```

## Creating a custom formatter

Your formatter should implement the `PriceFormatterInterface` and have a constructor was accepts and sets
the `$value`, `$currency` and `$unitQty` properties.

```php
<?php

namespace Lunar\Pricing;

use Illuminate\Support\Facades\App;
use Lunar\Models\Currency;
use NumberFormatter;

class CustomPriceFormatter implements PriceFormatterInterface
{
    public function __construct(
        public int $value,
        public ?Currency $currency = null,
        public int $unitQty = 1
    ) {
        if (! $this->currency) {
            $this->currency = Currency::getDefault();
        }
    }

    public function decimal(): float
    {
        // ...
    }

    public function unitDecimal(): float
    {
        // ...
    }

    public function formatted(): mixed
    {
        // ...
    }

    public function unitFormatted(): mixed
    {
        // ...
    }
}
```

The methods you implement can accept any number of arguments you want to support, you are not bound to what
the `DefaultPriceFormatter` accepts.

Once you have implemented the required methods, simply swap it out in `config/lunar/pricing.php`:

```php
return [
    // ...
    'formatter' => \App\Pricing\CustomPriceFormatter::class,
];
```

## Model Casting

If you have your own models which you want to use price formatting for, Lunar has a cast class you can use. The only
requirement is the column returns an `integer`.

```php
class MyModel extends Model
{
    protected $casts = [
        //...
        'price' => \Lunar\Base\Casts\Price::class
    ];
}
```