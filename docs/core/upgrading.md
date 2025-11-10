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

## 1.0.0 (stable)

### Medium Impact

#### Scout no longer enabled by default for admin panel
With the recent panel search improvements and the fact most users initially will not be using Scout, the default 
configuration for the panel is now to *not* enable Scout for search.

If you are using Scout, you may need to update your `panel.php` config.

```php
'scout_enabled' => true,
```


### Low Impact

#### Telemetry
This release introduces anonymous usage insights, which are sent via a deferred API call to Lunar. The reason for this addition
is to allow us to have an idea of how Lunar is being used and at what capacity. We do not send or use any identifying information whatsoever.

This is completely optional, however, it is turned on by default. To opt-out add the following to your service provider's boot method:

```php
\Lunar\Facades\Telemetry::optOut();
```

## 1.0.0-beta.24

### Medium Impact

#### Customer `vat_no` field renamed
The field on the `customers` table has been renamed to `tax_identifier`. This is to align with the new field of the same
name on `addresses`, `cart_addresses` and `order_addresses`.

### Low Impact

#### Buy X Get Y Discount conditions and rewards

Buy X Get Y discounts can now use collections and variants as conditions, and variants as rewards. As part of this change the `discount_purchasables` table has been renamed `discountables` and has its own `Discountable` model. If you have been using `discount_purchasables` directly, or the `purchasables` relation on the discount model, you will need to update your code.

## 1.0.0-beta.22

### High Impact

#### Removed Laravel 10 Support

Laravel 10 support has been removed as it was becoming harder to support. You will want to upgrade your projects to 
Laravel 11+ for this release. You may consider [Laravel Shift](https://laravelshift.com/) to assist you.

#### Lunar Panel Discount Interface

The `LunarPanelDiscountInterface` now requires a `lunarPanelRelationManagers` method that returns an array of relation managers you want to show in the admin panel when the discount type is used. You will need to update any custom discount types you have created to include this method.

## 1.0.0-beta.21

### High Impact

#### Order reference generation changes

The current order reference generator uses the format `YYYY-MM-{X}` which has been implemented since the early days of when Lunar was called GetCandy.

This approach to formatting is not great for order references and can lead to anomalies when attempting to determine the next reference in the sequence.

The new format uses the Order ID and adds leading zeros and an optional prefix i.e.

Assuming order ID is 1965
```
// Old
2025-04-00250
// New
00001965
```
The length of the reference, plus the prefix can now be defined in the `lunar/orders.php` config file:

```php
    'reference_format' => [
        /**
         * Optional prefix for the order reference
         */
        'prefix' => null,
        
        /**
         * STR_PAD_LEFT: 00001965
         * STR_PAD_RIGHT: 19650000
         * STR_PAD_BOTH: 00196500
         */
        'padding_direction' => STR_PAD_LEFT,
        
        /**
         * 00001965
         * AAAA1965
         */
        'padding_character' => '0',
        
        /**
         * If the length specified below is smaller than the length
         * of the Order ID, then no padding will take place.
         */
        'length' => 8,
    ],
```

If you wish to keep using the current action used to generate references, you can [copy the existing class](https://github.com/lunarphp/lunar/blob/1.0.0-beta20/packages/core/src/Base/OrderReferenceGenerator.php) into your app and update the `reference_generator` path in config.

### Medium Impact

#### Two-Factor Authentication has been added

To continue improving security for the Lunar panel, Staff members now have the ability to set up Two-Factor Authentication. Currently this is opt-in, however you can enforce all Staff members to set up 2FA:

```php
public function register()
{
    \Lunar\Admin\Support\Facades\LunarPanel::enforceTwoFactorAuth()->register();
}
```

If you do not wish to use Two-Factor Authentication at all, you can disable it and the option to set it up won't show.

```php
public function register()
{
    \Lunar\Admin\Support\Facades\LunarPanel::disableTwoFactorAuth()->register();
}
```

## 1.0.0-beta.1

### High Impact

#### Model Extending

Model extending has been completely rewritten and will require changes to your Laravel app if you have previously extended Lunar models.

The biggest difference is now Lunar models implement a contract (interface) and support dependency injection across your storefront and the Lunar panel.

You will need to update how you register models in Lunar.

##### Old
```php
    ModelManifest::register(collect([
        Product::class => \App\Models\Product::class,
        // ...
    ]));
```

##### New
```php
ModelManifest::replace(
    \Lunar\Models\Contracts\Product::class,
    \App\Models\Product::class
);
```

::: tip
If your models are not extending their Lunar counterpart, you must implement the relevant contract in `Lunar\Models\Contracts`
:::

Look at the [model extending](/core/extending/models) section for all available functionality.

#### Polymorphic relationships

In order to support model extending better, all polymorphic relationships now use an alias instead of the fully qualified class name, this allows relationships to resolve to your custom model when interacting with Eloquent.

There is an additional config setting in `config/lunar/database.php`, where you can set whether these polymorph mappings should be prefixed in Lunar's context.

```php
'morph_prefix' => null,
```

By default, this is set as `null` so the mapping for a product would just be `product`.

There is a migration which will handle this change over for Lunar tables and some third party tables, however you may need to add your own migrations to other tables or to switch any custom models you may have.

#### Shipping methods availability

Shipping methods are now associated to Customer Groups. If you are using the shipping addon then you should ensure that all your shipping methods are associated to the correct customer groups.

#### Stripe Addon

The Stripe addon will now attempt to update an order's billing and shipping address based on what has been stored against the Payment Intent. This is due to Stripe not always returning this information during their express checkout flows. This can be disabled by setting the `lunar.stripe.sync_addresses` config value to `false`.

##### PaymentIntent storage and reference to carts/orders
Currently, PaymentIntent information is stored in the Cart model's meta, which is then transferred to the order when created.

Whilst this works okay it causes for limitations and also means that if the carts meta is ever updated elsewhere, or the intent information is removed, then it will cause unrecoverable loss.

We have now looked to move away from the payment_intent key in the meta to a StripePaymentIntent model, this allows us more flexibility in how payment intents are handled and reacted on. A StripePaymentIntent will be associated to both a cart and an order.

The information we store is now:

- `intent_id` - This is the PaymentIntent ID which is provided by Stripe
- `status` - The PaymentIntent status
- `event_id` - If the PaymentIntent was placed via the webhook, this will be populated with the ID of that event
- `processing_at` - When a request to place the order is made, this is populated
- `processed_at` - Once the order is placed, this will be populated with the current timestamp

##### Preventing overlap
Currently, we delay sending the job to place the order to the queue by 20 seconds, this is less than ideal, now the payment type will check whether we are already processing this order and if so, not do anything further. This should prevent overlaps regardless of how they are triggered.

## 1.0.0-alpha.34

### Medium Impact

#### Stripe Addon

The Stripe driver will now check whether an order has a value for `placed_at` against an order and if so, no further processing will take place.

Additionally, the logic in the webhook has been moved to the job queue, which is dispatched with a delay of 20 seconds, this is to allow storefronts to manually process a payment intent, in addition to the webhook, without having to worry about overlap.

The Stripe webhook ENV entry has been changed from `STRIPE_WEBHOOK_PAYMENT_INTENT` to `LUNAR_STRIPE_WEBHOOK_SECRET`.

The stripe config Lunar looks for in `config/services.php` has changed and should now look like:

```php
'stripe' => [
    'key' => env('STRIPE_SECRET'),
    'public_key' => env('STRIPE_PK'),
    'webhooks' => [
        'lunar' => env('LUNAR_STRIPE_WEBHOOK_SECRET'),
    ],
],
```

## 1.0.0-alpha.32

### High Impact

There is now a new `LunarUser` interface you will need to implement on your `User` model.

```php
// ...
class User extends Authenticatable implements \Lunar\Base\LunarUser
{
    use \Lunar\Base\Traits\LunarUser;
}
```

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

## 1.0.0-alpha

:::warning
If upgrading from `0.x` please ensure you are on `0.8` before upgrading to `1.x`.
:::

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
