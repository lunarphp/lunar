# Taxation

[[toc]]

## Overview

No one likes taxes! But we have to deal with them... GetCandy provides manual tax rules to implement the correct sales tax for each order. For complex taxation (e.g. US States) we suggest integrating with a service such as [TaxJar](https://www.taxjar.com/).


## Tax Classes

Tax Classes are assigned to Products and allow us to clasify products to certain taxable groups that may have differing tax rates.

```php
GetCandy\Models\TaxClass
```

|Field|Description|
|:-|:-|
|id||
|name|e.g. `Clothing`|
|created_at||
|updated_at||

```php
$taxClass = TaxClass::create([
    'name' => 'Clothing',
]);
```

## Tax Zones

These specify a geographic zone for tax rates to be applied. Tax Zones can be based upon countries, states or zip/post codes.

```php
GetCandy\Models\TaxZone
```

|Field|Description|
|:-|:-|
|id||
|name|e.g. `UK`|
|zone_type|`country`, `state`, or `postcode`|
|price_display|`tax_inclusive` or `tax_exclusive`|
|active|true/false|
|default|true/false|
|created_at||
|updated_at||

```php
$taxZone = TaxZone::create([
    'name' => 'UK',
    'zone_type' => 'country',
    'price_display' => 'tax_inclusive',
    'active' => true,
    'default' => true,
]);
```

```php
GetCandy\Models\TaxZoneCountry
```

|Field|Description|
|:-|:-|
|id||
|tax_zone_id||
|country_id||
|created_at||
|updated_at||


```php
$taxZone->countries()->create([
    'country_id' => \GetCandy\Models\Country::first()->id,
]);
```

```php
GetCandy\Models\TaxZoneState
```

|Field|Description|
|:-|:-|
|id||
|tax_zone_id||
|state_id||
|created_at||
|updated_at||

```php
$taxZone->states()->create([
    'state_id' => \GetCandy\Models\State::first()->id,
]);
```

```php
GetCandy\Models\TaxZonePostcode
```

|Field|Description|
|:-|:-|
|id||
|tax_zone_id||
|country_id||
|postcode|wildcard, e.g. `9021*`|
|created_at||
|updated_at||

```php
GetCandy\Models\TaxZoneCustomerGroup
```

|Field|Description|
|:-|:-|
|id||
|tax_zone_id||
|customer_group_id||
|created_at||
|updated_at||


## Tax Rates

Tax Zones have one or many tax rates. E.g. you might have a tax rate for the State and also the City, which would collectively make up the total tax amount.

```php
GetCandy\Models\TaxRate
```

|Field|Description|
|:-|:-|
|id||
|tax_zone_id||
|name|e.g. `UK`|
|created_at||
|updated_at||

```php
GetCandy\Models\TaxRateAmount
```

|Field|Description|
|:-|:-|
|id||
|tax_rate_id||
|tax_class_id||
|percentage|e.g. `6` for 6%|
|created_at||
|updated_at||


## Settings
- Shipping and other specific costs are assigned to tax classes in the settings.
- Calculate tax based upon Shipping or Billing address?
- Default Tax Zone

## Extending

Taxation is a tricky business and sometimes what GetCandy offers simply won't be enough, and we completely understand. This why Taxation is now driver based, so you can add your own logic if you need to.

By default we have a `SystemTaxManager` which will use GetCandy's internal models and database as outlined above. If you need to write our own implementation, or if you're creating an add on for Tax, you can change the driver in the `config/taxes.php` config file.

```php
<?php

return [
    'driver' => 'system',
];
```

### Writing your own driver

To write your own driver you need to add a class which implements the `GetCandy\Base\TaxManager` interface and has the following methods:

```php
<?php

namespace App\Drivers;

use GetCandy\Base\TaxDriver;

class TaxJar implements TaxDriver
{
    /**
     * Set the shipping address.
     *
     * @param  \GetCandy\DataTypes\Address|null  $address
     * @return self
     */
    public function setShippingAddress(Address $address = null)
    {
        //
    }

    /**
     * Set the currency.
     *
     * @param  \GetCandy\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency)
    {
        //
    }

    /**
     * Set the billing address.
     *
     * @param  \GetCandy\DataTypes\Address|null  $address
     * @return self
     */
    public function setBillingAddress(Address $address = null)
    {
        //
    }

    /**
     * Set the purchasable item.
     *
     * @param  \GetCandy\Base\Purchasable|null  $address
     * @return self
     */
    public function setPurchasable(Purchasable $purchasable)
    {
        //
    }

    /**
     * Return the tax breakdown from a given sub total.
     *
     * @param  int  $subTotal
     */
    public function getBreakdown($subTotal)
    {
        //
    }
}
```

Once you have that, just extend the tax manager in your service provider.

```php

public function register()
{
    \GetCandy\Managers\TaxManager::extend('taxjar', function ($app) {
        return $app->make(TaxJar::class);
    })
}
```

You can then set this as the driver in the taxes config.
