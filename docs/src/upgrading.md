# Upgrading

[[toc]]


## General Upgrade Instructions

Update the package

```sh
composer update getcandy/admin
```

Run any migrations

```sh
php artisan migrate
```


Re-publish the admin hub assets

```sh
php artisan getcandy:hub:install
```

If you're using Meilisearch, run the following

```sh
php artisan getcandy:meilisearch:setup
```

## 2.0-beta11

There is a new configuration option under `getcandy-hub/products.php` to disable product variants. This is useful if your storefront will never need to generate different product options and you don't want staff members to be able to do it accidentally.

```
'disable_variants' => false,
```

If your storefront already supports variants, you do not need to change anything.

If you disable variants, the `GenerateVariants` job will now throw an exception if it's called when this setting is `true` so you will need to update any calls to this job to handle it.

```php
GetCandy\Hub\Exceptions\VariantsDisabledException
```

---

If you are using the scout `Searchable` trait. Make sure to change this to GetCandy's if you want to tap into the Model Observers.

```php
// Old
use Laravel\Scout\Searchable;

// New
use GetCandy\Base\Traits\Searchable;
```

---

### Changes to order statuses - High Impact

The way statuses for orders are defined in `config/getcandy/orders.php` has changed. See below for the new definition:

#### Old

```php
'statuses'  => [
    'awaiting-payment' => 'Awaiting Payment',
    'payment-received' => 'Payment Received',
],
```

#### New

```php
'statuses'  => [
    'awaiting-payment' => [
        'label' => 'Awaiting Payment',
        'color' => '#848a8c',
    ],
    'payment-received' => [
        'label' => 'Payment Received',
        'color' => '#6a67ce',
    ],
],
```

### Changes to index naming - High Impact

You must re index and set up Meilisearch indexes due to a breaking change.

```sh
php artisan getcandy:search:index
```

```sh
php artisan getcandy:meilisearch:setup
```

This change removes the `_{locale}` suffix from certain indexes, so those can be removed.

### Changes to URL generation - High Impact

There is a new config file under `config/urls.php` which will define if URL's should be generated for models that use them and how they should be generated. By default this has been set to `true` so URL's are automatically generated.

If you have your own routine for URL's then you should either implement your own generator and set it to the `generator` config option, or turn off automatic generation.

## 2.0-beta10

### Changes to Tax drivers - High Impact

Previously tax drivers were required to return a collection of `GetCandy\Models\TaxRateAmount` models. This wasn't very useful for custom tax drivers that did not use them and as a result limited their use. The interface was also not very clear on what should be returned.

The interface has been updated to make this clearer.

All methods that set properties on the tax driver should now add the `self` return type.

```php
// Before
public function setCurrency(Currency $currency);

// After
public function setCurrency(Currency $currency): self;
```

The return type for the `getBreakdown` method should now be as follows:

```php
public function getBreakdown($subTotal): \GetCandy\Base\DataTransferObjects\TaxBreakdown;
```

You need to update the `getBreakdown` method to use both the new Data Transfer Objects.

```php
public function getBreakdown($subTotal): TaxBreakdown
{
    $breakdown = new TaxBreakdown;

    $amount = new TaxBreakdownAmount(
        price: new Price(1234, $this->currency, 1),
        description: 'VAT',
        identifier: 'vat',
        percentage: 20.00
    );

    $breakdown->addAmount($amount);

    return $breakdown;
}
```

A new `setCartLine` method has been added and when we calculate the tax for the cart line it is passed through and available in your own tax driver. This property is nullable so you should check it's existence before relying on it.

```php
public function getBreakdown($subTotal): TaxBreakdown
{
    // ...
    $this->cartLine;
}
```

### Demo store updates

If you are using the demo store. You should update the reference to the tax description on the checkout.

```
resources/views/livewire/checkout-page.blade.php
```

```html
<!-- Old -->
{{ $tax['rate']->name }}

<!-- New -->
{{ $tax['description'] }}
```

## 2.0-beta9

There shouldn't be any additional steps to take for this release.

## 2.0-beta8

### Handles now use `Str::handle` helper - Medium Impact

When creating new attributes, a design decision has been made to force `snake_case`. Existing attributes shouldn't affected, however if you want to bring your store inline with this change, will need to update each attribute handle and then also update any `attribute_data` to use the know formatting.

### Description attribute is no longer required or a system attribute

On install we no longer set `description` to be `system` or `required` as this was causing issues when trying to edit the attribute. Simply remove the `system` and `required` flags from the `description` attribute in the database.

## 2.0-beta7

This version adds a new config setting for User ID field types.

Please add the following to your `config/getcandy/database.php` file

```
    /*
    |--------------------------------------------------------------------------
    | Users Table ID
    |--------------------------------------------------------------------------
    |
    | GetCandy adds a relationship to your 'users' table and by default assumes
    | a 'bigint'. You can change this to either an 'int' or 'uuid'.
    |
    */
    'users_id_type' => 'bigint',
```

### Channel availability - High Impact

The signature for scheduling a model for a channel has changed:

Old

```php
$product->scheduleChannel($channel, now()->addDays(14));
```

New

```php
$product->scheduleChannel($channel, $startAt, $endAt);
```

`$startAt` and `$endAt` should be either `DateTime` objects or `null`.


## v2.0-beta5

The composer package to install has now changed to `getcandy/admin`. This is to support our new monorepo [getcandy/getcandy](https://github.com/getcandy/getcandy)

To get this update you need to make a change in your composer file.

From
```
"getcandy/getcandy": "^2.0"
```

To
```
"getcandy/admin": "^2.0"
```

And then run...

```sh
composer update
```

Then re-publish the admin hub assets

```sh
php artisan getcandy:hub:install
```

## v2.0-beta

GetCandy 2 is a complete re-write of our e-commerce page. It is not currently possible to upgrade from v0.12.* to GetCandy 2.

GetCandy 2 provides both the core e-commerce functionality and also an integrated admin hub within Laravel. A separate package will be released early 2022 to provide frontend API functionality.


## Migrating from v0.12.*

::: warning Planned
We intend to release an upgrade utility before v2 is out of beta.
:::
