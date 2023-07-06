# Configuration

## Overview

Configuration for Lunar is separated into individual files under `config/lunar` for core and `config/lunar-hub` for admin hub. You can either override the different config options adhoc or you can publish all the configuration options and tweak as you see fit.

```bash
php artisan vendor:publish --tag=lunar
```

### Database Table Prefix

`lunar/database.php`


So that Lunar tables do not conflict with your existing application database tables, you can specify a prefix to use. If you change this after installation, you are on your own - happy renaming!

```php
    'table_prefix' => 'lunar_',
```

### Database Connection

`lunar/database.php`

 By default, the package uses the default database connection defined in Laravel. Here specify a custom database connection for Lunar.

```php
    'connection' => 'some_custom_connection',
```

If you are using a custom database connection that is not the default connection in your Laravel configuration, you need to specify it in the .env file as well.

```
    ACTIVITY_LOGGER_DB_CONNECTION=some_custom_connection
```
In our package, we utilize Spatie's [laravel-activitylog](https://spatie.be/docs/laravel-activitylog) for logging. The mentioned configuration allows the activity logger to use a different database connection instead of the default database connection.

### Orders

`lunar/orders.php`

Here you can set up the statuses you wish to use for your orders.

```php
    'draft_status' => 'awaiting-payment',
    'statuses' => [
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

```
    'stored_inclusive_of_tax' => false,
```

