# Currencies

[[toc]]

## Overview

Currencies allow you to charge different amounts relative to the currency you're targeting.

## Creating a currency

```php
\GetCandy\Models\Currency::create([
    'code' => 'GBP',
    'name' => 'British Pound',
    'exchange_rate' => 1.0000,
    'format' => '£{value}',
    'decimal_point' => '.',
    'thousand_point' => ',',
    'enabled' => 1,
    'default' => 1,
]);
```

|Field|Description|
|:-|:-|
|`code`|The should be the `ISO 4217` currency code. |
|`name`|A given name for the currency.|
|`exchange_rate`|This should be the exchange rate relative to the default currency (see below)|
|`format`|The given format for displaying any amount using this currency. (see currency format below)|
|`decimal_point`|Specify the decimal point, i.e. `.` for `1,000.00`|
|`thousand_point`|Specify the thousand point, i.e. `,` for `1,000.00`|
|`enabled`|Whether the currency is enabled|
|`default`|Whether the currency is the default|

## Currency Format

The format in which currencies can appear can all potentially be different, to facilitate this GetCandy has a `format` column which you can specify how prices are displayed.

For example, the format for GBP is `£1.99` We can then specify this format in GetCandy by setting the `format` to `£{value}`.

If we wanted to format for EUR, which might be `1,99€` we would simply do `{value}€`. So what's happening?

The steps we take are:

  1. Assume we have the number `199` (as cents)
  2. We divide by `100` and use the `decimal_point` and `thousand_point` defined on the currency.
  3. We then take the resulting number and replace `{value}` with it.

This then gives us the correct format and allows it to be specified per currency.

::: warning
`{value}` must always be present, otherwise the price will not be swapped out correctly.
:::

## Exchange rates
These are relative to the default currency. For example assuming we have the following:

```php
\GetCandy\Models\Currency::create([
    'code' => 'GBP',
    'name' => 'British Pound',
    'exchange_rate' => 1.0000,
    'format' => '£{value}',
    'decimal_point' => '.',
    'thousand_point' => ',',
    'enabled' => 1,
    'default' => 1,
]);
```

We set the model to be the default and we set the exchange rate to be `1` as we're defining this as our base (default) currency.

Now, say we wanted to add EUR (Euros). Currently the exchange rate from GBP to EUR is `1.17`. But we want this to be relative to our default record. So 1 / 1.17 = 0.8547.

It's entirely up to you what you want to set the exchange rates as, it is also worth mentioning that this is independent of product pricing in the sense that you can specify the price per currency. The exchange rate serves as a helper when working with prices so you have something to go by.
