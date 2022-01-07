# Configuration

[[toc]]

## Overview

Configuration for GetCandy is separated into individual files under `config/getcandy`. You can either override the different config options adhoc or you can publish all the configration options and tweak as you see fit.

```bash
php artisan vendor:publish --tag=getcandy
```

### Database Table Prefix

`getcandy/database.php`

So that GetCandy tables do not conflict with your existing application database tables, you can specify a prefix to use. If you change this after installation, you are on your own - happy renaming!

```php
    'table_prefix' => 'getcandy_',
```

### Orders

`getcandy/orders.php`

Here you can set up the statuses you wish to use for your orders.

```php
    'draft_status' => 'awaiting-payment',
    'statuses' => [
        'awaiting-payment' => 'Awaiting Payment',
        'dispatched' => 'Dispatched',
    ],
```


### Media

`getcandy/media.php`

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
