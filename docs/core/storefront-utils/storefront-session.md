# Storefront Session

## Overview

The storefront session facade is a provided to help keep certain resources your storefront needs set, such as channel, customer group etc.

```php
use Lunar\Facades\StorefrontSession;
```

## Channels

### Initialise the Channel

This will set the Channel based on what's been previously set, otherwise it will use the default.

```php
StorefrontSession::initChannel();
```

:::tip This is automatically called when using the facade.
:::

### Set the Channel

```php
$channel = new Channel([
    'name' => 'Webstore',
    'handle' => 'webstore',
]);

StorefrontSession::setChannel($channel);
StorefrontSession::setChannel('webstore');
```

### Get the Channel

```php
StorefrontSession::getChannel();
```

## Customer Groups

### Initialise the Customer Group

This will set the Customer Group based on what's been previously set, otherwise it will use the default.

```php
StorefrontSession::initCustomerGroup();
```

:::tip This is automatically called when using the facade.
:::

### Set the Customer Group

```php
$customerGroup = new CustomerGroup([
    'name' => 'Retail',
    'handle' => 'retail',
]);

StorefrontSession::setCustomerGroup($customerGroup);
StorefrontSession::setCustomerGroup('retail');
```

### Get the Customer Group

```php
StorefrontSession::getCustomerGroup();
```

## Currencies

### Set the Currency

```php
$currency = new Currency([
    'name' => 'US Dollars',
    'code' => 'USD',
    // ...
]);

StorefrontSession::setCurrency($currency);
StorefrontSession::setCurrency('USD');
```

### Get the Currency

```php
StorefrontSession::getCurrency();
```
