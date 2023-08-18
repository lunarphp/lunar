# Addresses

## Overview

Customers may save addresses to make checking-out easier and quicker.

## Addresses

```php
Lunar\Models\Address
```

|Field|Description|
|:-|:-|
|`id`||
|`customer_id`||
|`title`|nullable|
|`first_name`||
|`last_name`||
|`company_name`|nullable|
|`line_one`||
|`line_two`|nullable|
|`line_three`|nullable|
|`city`||
|`state`|nullable|
|`postcode`|nullable|
|`country_id`||
|`delivery_instructions`||
|`contact_email`||
|`contact_phone`||
|`last_used_at`|Timestamp for when the address was last used in an order.|
|`meta`|JSON|
|`shipping_default`|Boolean|
|`billing_default`|Boolean|
|`created_at`||
|`updated_at`||

## Countries

```php
Lunar\Models\Country
```

|Field|Description|
|:-|:-|
|`id`||
|`name`||
|`iso3`||
|`iso2`||
|`phonecode`||
|`capital`||
|`currency`||
|`native`||
|`emoji`|Flag|
|`emoji_u`|Flag|
|`created_at`||
|`updated_at`||


## States

```php
Lunar\Models\State
```

|Field|Description|
|:-|:-|
|`id`||
|`country_id`||
|`name`||
|`code`||
|`created_at`||
|`updated_at`||

## Address Data

Data for Countries and States is provided by https://github.com/dr5hn/countries-states-cities-database.

You can use the following command to import countries and states.

```sh
php artisan lunar:import:address-data
```
