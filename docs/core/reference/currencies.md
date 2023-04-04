# Currencies

## Overview

Currencies allow you to charge different amounts relative to the currency you're targeting.

## Creating a currency

```php
\Lunar\Models\Currency::create([
    'code' => 'GBP',
    'name' => 'British Pound',
    'exchange_rate' => 1.0000,
    'decimal_places' => 2,
    'enabled' => 1,
    'default' => 1,
]);
```

|Field|Description|
|:-|:-|
|`code`|The should be the `ISO 4217` currency code. |
|`name`|A given name for the currency.|
|`exchange_rate`|This should be the exchange rate relative to the default currency (see below)|
|`decimal_places`|Specify the decimal places, e.g. 2|
|`enabled`|Whether the currency is enabled|
|`default`|Whether the currency is the default|

## Exchange rates
These are relative to the default currency. For example assuming we have the following:

```php
\Lunar\Models\Currency::create([
    'code' => 'GBP',
    'name' => 'British Pound',
    'exchange_rate' => 1.0000,
    'decimal_places' => 2,
    'enabled' => 1,
    'default' => 1,
]);
```

We set the model to be the default and we set the exchange rate to be `1` as we're defining this as our base (default) currency.

Now, say we wanted to add EUR (Euros). Currently the exchange rate from GBP to EUR is `1.17`. But we want this to be relative to our default record. So 1 / 1.17 = 0.8547.

It's entirely up to you what you want to set the exchange rates as, it is also worth mentioning that this is independent of product pricing in the sense that you can specify the price per currency. The exchange rate serves as a helper when working with prices so you have something to go by.
