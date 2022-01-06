# Upgrading

[[toc]]

## How to Upgrade v2

To upgrade to the latest v2 release, follow these steps.

### Update the Composer package.

```sh
composer update getcandy/getcandy
```

### Run Migrations

```sh
php artisan migrate
```

### Re-Publish Hub Assets

```sh
php artisan getcandy:hub:install --force
```

## Migrating from v0.12.*

::: warning Planned
We intend to release an upgrade utility before v2 is out of beta.
:::

