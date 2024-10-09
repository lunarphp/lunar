# Configuration

## Overview

Configuration for Lunar is separated into individual files under `config/lunar`. 
You can either override the different config options adhoc or you can publish all the configuration options and tweak 
as you see fit.

```bash
php artisan vendor:publish --tag=lunar
```

## Cart
`config/lunar/cart.php`

| Setting                          | Description                                                                                                                                                             |
|:---------------------------------|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| lunar.cart.session_key           | This value is used to store the cart information in the Laravel session. If for any reason you already have a session entry with the same name, you can change it here. |
| lunar.cart.fingerprint_generator | The fingerprint generator class is specified here, which provides methods to check if a cart has changed or not.                                                        |
| lunar.cart.auto_create           | Whether Lunar should automatically create a cart when one doesn't exist. Generally best left as `false`.                                                                |
| lunar.cart.auth_policy           | When a user logs in the auth policy determines what should happen to the cart if the user had a previous cart when logged in. Can either be 'merge' or 'override'.      |
| lunar.cart.pipelines             | The code that run when the cart is calculating. You can make your own actions to customise how the cart calculations work.                                              |
| lunar.cart.actions               | The action that is used by the cart when adding, updating, etc. If you need to tweak some cart logic, it can be done by switching out these classes.                    |
| lunar.cart.validators            | Allows you to customise the cart validation rules.                                                                                                                      |
| lunar.cart.eager_load            | Determines the eager loading applied when the cart loads. We set a suitable default and you can customise for your own app's performance.                               |

## Database
`config/lunar/database.php`

| Setting                           | Description                                                                                                                                                                                                     |
|:----------------------------------|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| lunar.database.connection         | By default, the package uses the default database connection. If you change connection, be sure to also set `ACTIVITY_LOGGER_DB_CONNECTION=` in your ENV file so that Spatie ActivityLog continues to function. |
| lunar.database.table_prefix       | So that Lunar tables do not conflict with your existing application database tables, you can specify a prefix to use. If you change this after installation, you are on your own - happy renaming!              |
| lunar.database.users_id_type      |                                                                                                                                                                                                                 |
| lunar.database.disable_migrations |                                                                                                                                                                                                                 |

So that Lunar tables do not conflict with your existing application database tables, you can specify a prefix to use. If you change this after installation, you are on your own - happy renaming!

## Discounts

## Media

## Orders
`config/lunar/orders.php`

| Setting                          | Description                                                                                                                                                 |
|:---------------------------------|:------------------------------------------------------------------------------------------------------------------------------------------------------------|
| lunar.orders.reference_generator | Defines a class which is responsible for generating order references.                                                                                       |
| lunar.orders.statuses            | Here you can set up the statuses you wish to use for your orders. See the [Orders](reference/orders.html#order-notifications) section for more information. |
| lunar.orders.pipelines           | The code that run when the order is processing. You can make your own code to customise how the order processing works.                                     |

## Payments

```php
'connection' => 'some_custom_connection',
```

## Search

```
ACTIVITY_LOGGER_DB_CONNECTION=some_custom_connection
```

In our package, we utilize Spatie's [laravel-activitylog](https://spatie.be/docs/laravel-activitylog) for logging. The mentioned configuration allows the activity logger to use a different database connection instead of the default database connection.

### Media

`lunar/media.php`

Transformations for all uploaded images.

```php
'transformations' => [
    'zoom' => [
        'width' => 500,
        'height' => 500,
    ],
    'large' => [
        // ...
    ],
    'medium' => [
        // ...
    ],
    'small' => [
        // ...
    ],
],
```

### Products

`lunar-hub/products.php`

```php
'disable_variants' => false,
'sku' => [
    'required' => true,
    'unique'   => true,
],
'gtin' => [
    'required' => false,
    'unique'   => false,
],
'mpn' => [
    'required' => false,
    'unique'   => false,
],
'ean' => [
    'required' => false,
    'unique'   => false,
],
```

### Pricing
`lunar/pricing.php`

If you want to store pricing inclusive of tax then set this config value to `true`.

```php
'stored_inclusive_of_tax' => false,
```

## Shipping

## Taxes

## URLs
