# Upgrading

## General Upgrade Instructions

Update the package

```sh
composer update lunarphp/lunar
```

Run any migrations

```sh
php artisan migrate
```

## Support Policy

Lunar currently provides bug fixes and security updates for only the latest minor release, e.g. `0.8`.

## 1.0.0-alpha.31

### High Impact

Certain parts of `config/cart.php` which are more specific to when you are interacting with carts via the session have been relocated to a new `config/cart_session.php` file.

```php
// Move to config/cart_session.php
'session_key' => 'lunar_cart',
'auto_create' => false,
```

You should also check this file for any new config values you may need to add.

## 1.0.0-alpha.29

### High Impact

#### Cart calculate function will no longer recalculate

If you have been using the `$cart->calculate()` function it has previously always run the calculations regardless of 
whether the cart has already been calculated. Now the calculate function will only run if we don't have cart totals. 
To allow for recalculation we have now introduced `$cart->recalculate()` to force the cart to recalculate.

#### Unique index for Collection Group handle

Collection Group now have unique index on the column `handle`.
If you are creating Collection Group from the admin panel, there is no changes required.

### Medium Impact

#### Update custom shipping modifiers signature

The `\Lunar\Base\ShippingModifier` `handle` method now correctly passes a closure as the second parameter. You will need to update any custom shipping modifiers that extend this as follows:

```php
public function handle(\Lunar\Models\Cart $cart, \Closure $next)
{
    //..
    
    return $next($cart);
}
```

## 1.0.0-alpha.26

### Medium Impact

If you are using your own classes that implement the `Purchasable` interface, you will need to add the following additional methods:

```php
public function canBeFulfilledAtQuantity(int $quantity): bool;
public function getTotalInventory(): int;
```

If you are checking the `ProductVariant` `purchasable` attribute in your code, you should update the following check:

```php
// Old
$variant->purchasable == 'backorder';
// New
$variant->purchasable == 'in_stock_or_on_backorder';

```

## 1.0.0-alpha.22

### Medium Impact

Carts now use soft deletes and a cart will be deleted when `CartSession::forget()` is called.
If you don't want to delete the cart when you call `forget` you can pass `delete: false` as a parameter:

```php
\Lunar\Facades\CartSession::forget(delete: false);
```

## 1.0.0-alpha.20

### High Impact

#### Stripe addon facade change

If you are using the Stripe addon, you need to update the facade as the name has changed.

```php
// Old
\Lunar\Stripe\Facades\StripeFacade;

// New
\Lunar\Stripe\Facades\Stripe;
```

## 1.0

### High Impact

#### Change to Staff model namespace

The Staff model has changed location from `Lunar\Hub\Models\Staff` to `Lunar\Admin\Models\Staff` so this will need to be updated within
your codebase and any polymorphic relations.

#### Spatie Media Library
This package has been upgrade to version 11, which introduces some breaking changes.
See here for more information https://github.com/spatie/laravel-medialibrary/blob/main/UPGRADING.md

#### Media Conversions
The `lunar.media.conversions` configuration has been removed, in favour of registering custom media definitionss instead.
Media definition classes allow you to register media collections, conversions and much more. See [Media Collections](/core/reference/media.html#media-collections)
for further information.

#### Product Options
The `position` field has been removed from the `product_options` table and is now found on the `product_product_option` 
pivot table. Any position data will be automatically adjusted when running migrations.

#### Tiers renamed to Price Breaks

The `tier` column on pricing has been renamed to `min_quantity`, any references in code to `tiers` needs to be updated.

##### Price Model

```php
// Old
$priceModel->tier
// New
$priceModel->min_quantity

// Old
$priceModel->tiers
// New
$priceModel->priceBreaks
```

##### Lunar\Base\DataTransferObjects\PricingResponse

```php
// Old
public Collection $tiered,
// New
public Collection $priceBreaks,
```

##### Lunar\Base\DataTransferObjects\PaymentAuthorize

Two new properties have been added to the constructor for this DTO.

```php
public ?int $orderId = null,
public ?string $paymentType = null
```

## 0.8

No significant changes.

## 0.7

### High Impact

#### TaxBreakdown casting has been refactored

Database columns which have `tax_breakdown` casting will now actually cast back into the `TaxBreakdown` object. This means you will need to update any storefront views or API transformers to accommodate this.

Before:

```php
@foreach ($order->tax_breakdown as $tax)
    {{ $tax->total->formatted }}
@endforeach
```

```php
@foreach ($order->tax_breakdown->amounts as $tax)
    {{ $tax->price->formatted }}
@endforeach
```

When migrations are run, a state update routine will trigger to convert all existing `tax_breakdown` column. Please ensure you take a backup of your database beforehand and avoid running in production until you are satisfied the data is correct.

### Medium Impact

#### Discount updates

Limitations and exclusions on discounts have had a revamp, please double-check all discounts you have in Lunar to ensure they are all correct. Generally speaking the integrity should be unaffected, but it's better to be sure.

#### Calculate lines pipeline update

If you are using unit quantities greater than `1`, there was an issue in the calculate lines pipeline which resulted in the unit quantity being applied twice, so if the price was `10` with a unit quantity of `100` it would show the unit price as `0.001` instead of `0.01`. This should be resolved going forward to show correctly.

### Low Impact

#### Click & Collect parameter added to `ShippingOption`

The `Lunar\DataTypes\ShippingOption` class now has an additional `collect` parameter. This can be used to determine whether the shipping option is considered "collect in store". This defaults to `false` so there are no additional steps if your store doesn't offer click and collect.

```php
ShippingManifest::addOption(
    new ShippingOption(
        name: 'Pick up in store',
        description: 'Pick your order up in store',
        identifier: 'PICKUP',
        price: new Price(/** .. */),
        taxClass: $taxClass,
        collect: true
    )
);
```


## 0.6

### High Impact

#### Search indexing refactor

Search indexing has been completely re-written to be more extendable and performant. You will need to migrate
any `Observer` classes that use the `indexer` event to the new indexer class implementation.

The following methods have also been removed from the `Searchable` trait and should be migrated.

- `addFilterableAttributes`
- `addSearchableAttributes`
- `addSortableAttributes`
- `getObservableEvents`

If you still wish to use these methods you will need to re-implement them yourself, however this is highly discouraged.

See the [`Search Extending`](/core/extending/search) guide for more information about what indexer classes are and how
to use them.

#### Licensing Manager has been removed

You will need ro re-run the addons discover command to update the manifest. No additional steps are required for addons.

```shell
$ php artisan lunar:addons:discover
````

## 0.5

### High Impact

#### `meta` field cast with `Illuminate\Database\Eloquent\Casts\AsArrayObject`

All models with `meta` attribute are now cast with
Laravel's [`AsArrayObject::class`](https://laravel.com/docs/10.x/eloquent-mutators#array-object-and-collection-casting).
Change your code to get the value
with `$model->meta['key'] ?? 'default'` instead of `$model->meta->key`, and without the need of
`is_object/is_array` type checking.

## 0.4

### High Impact

#### Changed Lunar Hub authorization to use `spatie/laravel-permission`

Existing assigned staff permissions are migrated, this should not impact your project.
If you have custom authorization checking using `Staff->authorize('permission')`, change it
to `Staff->hasPermissionTo('permission')`

#### ShippingManifestInterface

Added `addOptions`, `getOptionUsing`, `getOption`, `getShippingOption` to ShippingManifestInterface

#### MySQL 8.x Requirement

With MySQL 5.7 EOL coming in October 2023 and Lunar's heavy use of JSON fields, Lunar now only supports MySQL 8.x.
You may find your project continues to work fine in MySQL 5.7, but we advise upgrading.

#### Carts

- The `shippingTotal` property now includes the tax in the amount, use `shippingSubTotal` instead.
- A new `shippingBreakdown` property has been added which will include all shipping costs and be available to pipelines.

If you are modifying the shipping cost outside of your own shipping options in the shipping manifest, you should create
a custom cart pipeline and use the shipping breakdown property as this is where the shipping total will be calculated
from.

```php
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;

$shippingBreakdown = $cart->shippingBreakdown ?: new ShippingBreakdown;

$shippingBreakdown->items->put('ADDSHIP',
    new ShippingBreakdownItem(
        name: 'Additional Shipping Cost',
        identifier: 'ADDSHIP',
        price: new Price(123, $currency, 1),
    )
);
```

#### Cart/Order Relationship

The relationship between a cart and an order has been changed, previously the `carts` table had an `order_id` column,
this has been changed so the `cart_id` is now on the `orders` table.

You should update any code that sets the `order_id` on a cart to `cart_id` on the order.

We've also introduced the concept of `draft` and `complete` orders for carts, so you should update any code that
references an order from a cart to the following methods:

```php
// Old
$cart->order

// New

/* The order which doesn't have a `placed_at` value */
$cart->draftOrder

/* Any orders which have a `placed_at` value */
$cart->completedOrder
```

### Changes to `CreateOrder` action

The `Lunar\Actions\Cart\CreateOrder` action has been refactored to run through pipelines, much like how carts are
currently calculated. If you are currently using your own `CreateOrder` action, you should refactor the logic into
pipelines and ues the provided action.

:::danger
The `CreateOrder` class is now final, so if you are extending this action you will need to refactor your
implementation.
:::

See [Extending Orders](/core/extending/orders)

### Low impact

Add the new fingerprint class reference to `config/lunar/carts.php` if you have published the config.

```php
'fingerprint_generator' => Lunar\Actions\Carts\GenerateFingerprint::class,
```

## 0.3

### High Impact

#### Support for Laravel 8 removed

To install Lunar 0.3 you will need to be on at least Laravel 9. 0.3 introduces support for Laravel 10.

### Low Impact

#### Changed Auth guard to use Laravel's default driver.

This should not impact your project unless you have customised the admin hub authentication.

#### Updated `db_date` function to return just the formmatted string.

If you were using `db_date` helper function, you will now need to wrap it with `DB:RAW()`.

## 0.2.5

### Low Impact

If you have a custom DiscountType, you should update the `save` method to expect the recently saved discount id to be
passed.

```php
// Before
public function save();

// After
public function save($discountId);
```

## 0.2

### High Impact

#### Removal of the Cart Manager and Cart Modifiers

This release moves away from Cart Modifiers and the Cart Manager.

You can still extend the Cart in the form of pipelines. See [Cart Extending](extending/carts).

You need to replace any instances of `$cart->getManager()` and just reference the cart itself i.e.

```php
// Old
$cart->getManager()->setShippingOption();

// New
$cart->setShippingOption();
```

For calculating the cart totals you should change the following:

```php
// Old
$cart->getManager()->getCart();

// New
$cart->calculate();
```

## 0.1.0-rc.5

### Changed

#### Publishing changes

All publishing commands for Lunar now use `.` as a separator.

- Rename `lunar-hub-translations` to `lunar.hub.translations`
- Rename `lunar-hub-views` to `lunar.hub.views`
- Rename `lunar:hub:public` to `lunar.hub.public`
- Rename `lunar-migrations` to `lunar.migrations`

#### Brand requirement is now configurable.

Whether the product brand is required on your store is now configurable, the default behaviour is set to `true`. If you
wish to change this, simply update `config/lunar-hub/products.php`.

```php
'require_brand' => false,
```

---

## Migrating from GetCandy to Lunar

The initial release of Lunar will be version `0.1.0`. This allows for a rapid development cycle until we reach `1.0.0`.
Understandably, a complete name change is not small task, so we've outlined steps you need to take to bring your install
up to the latest Lunar version and move away from GetCandy.

### Update composer dependencies

```json
"getcandy/admin": "^2.0-beta",
"getcandy/core": "^2.0-beta"
```

```json
"lunarphp/lunar": "^0.1"
```

Any add-ons you are using will need their namespaces updated, the package name should remain the same, i.e.

```json
"getcandy/stripe": "^1.0"
```

```json
"lunarphp/stripe": "^0.1"
```

Once done, remember to run `composer update` to pull in the latest packages.

### Update namespaces

If you are using any GetCandy classes, such as models, you will need to update their namespace:

#### Models

```php
GetCandy\Models\Product;
```

```php
Lunar\Models\Product;
```

A simple find and replace in your code should be sufficient, the strings you should search for are:

```
GetCandy
get-candy
getcandy
```

### Config changes

- Rename the `config/getcandy` folder to `config/lunar`
- Rename the `config/getcandy-hub` folder to `config/lunar-hub`
- Change the prefix in `config/lunar/database.php` from `getcandy_` to `lunar_`

Also make sure any class references in your config files have been updated to the `Lunar` namespace.

### Meilisearch users

Lunar no longer ships with Meilisearch by default. If you use Meilisearch and wish to carry on using it, you will need
to require the new Lunar meilisearch package.

```sh
composer require lunarphp/meilisearch
```

This will install the appropriate packages that Scout needs and also register the set up command so you can keep using
it, you just need to update the signature.

```sh
php artisan lunar:meilisearch:setup
```

### MySQL Search

If you were previously using the `mysql` Scout driver, you should change this to `database_index`. This populates the
`search_index` table with the terms to be searched upon. You may need to run the scout import command:

```sh
php artisan scout:import Lunar\Models\Product
```

### Database migration

If you are using the `getcandy_` prefix in your database, then you will likely want to update this to `lunar_`.
We have created a command for this purpose to try make the switch as easy as possible.

```sh
php artisan lunar:migrate:getcandy
```

#### What this command will do

- Remove any previous GetCandy migrations from the `migrations` table.
- Run the migrations again with the `lunar_` prefix, creating new tables.
- Copy across the data from the old `getcandy_` tables into the new `lunar_` tables.
- Update any polymorphic `GetCandy` classes to the `Lunar` namespace.
- Update field types in `attribute_data` to the `Lunar` namespace.

#### What this command will not do

- Affect any custom tables that have been added outside the core packages.

---

The intention of this is to provide a non-destructive way to migrate the data. Once the command has been run
your `getcandy_` tables should remain intact, so you are free to check the data and remove when ready.
