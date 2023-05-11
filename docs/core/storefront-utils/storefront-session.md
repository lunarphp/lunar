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

### Initialise the Customer Groups

This will set the Customer Groups based on what's been previously set (from the session), otherwise it will use the default record.

```php
StorefrontSession::initCustomerGroups();
```

:::tip This is automatically called when using the facade.
:::

### Set the Customer Groups

```php
$customerGroup = new CustomerGroup([
    'name' => 'Retail',
    'handle' => 'retail',
]);

// Set multiple customer groups
StorefrontSession::setCustomerGroups(collect($customerGroup));

// Set a single customer group, under the hood this will just call `setCustomerGroups`.
StorefrontSession::setCustomerGroup($customerGroup);
```

### Get the Customer Groups

```php
StorefrontSession::getCustomerGroups();
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
