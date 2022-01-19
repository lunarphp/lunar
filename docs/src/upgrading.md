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

## [unreleased]

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

## 2.0-beta7

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
