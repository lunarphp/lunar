# Installation

::: danger Work In Progress
Version 1.x is very much in-development. It is incomplete and not production ready.
:::

## Server Requirements

- PHP ^8.1
- Laravel 10
- MySQL 8.0+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- intl PHP extension (on most systems it will be installed by default)
- bcmath PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Install Lunar

### Composer Require Package

```sh
composer require lunarphp/lunar:1.x-dev -W
```

::: tip
You may need to update your app's `composer.json` to set `"minimum-stability": "dev",`
:::

### Add the LunarUser Trait

Some parts of the core rely on the `User` model having certain relationships set up. We've bundled these into a trait which you must add to any models that represent users in your database.

```php
use Lunar\Base\Traits\LunarUser;
// ...

class User extends Authenticatable
{
    use LunarUser;
    // ...
}
```

## Register the admin panel

The admin panel needs registering in your app service provider before you can use it.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::register();
    }
```

### Run the Artisan Installer

```sh
php artisan lunar:install
```

This will take you through a set of questions to configure your Lunar install. The process includes...

- Creating a default admin user (if required)
- Seeding initial data
- Inviting you to star our repo on GitHub ⭐

You should now be able to access the panel at `https://<yoursite>/lunar`.

## Advanced Installation Options

Before you run the Lunar installer command, you may wish to customise some of the set-up.

### Publish Configuration

```sh
php artisan vendor:publish --tag=lunar
```

### Table Prefix

Lunar uses table prefixes to avoid conflicts with your app's tables. You can change this in the [configuration](/core/configuration.html).

### User ID Field Type

Lunar assumes your User ID field is a "BIGINT". If you are using an "INT" or "UUID", you will want to update the configuration in `config/lunar/database.php` to set the correct field type before running the migrations.

### Publish Migrations

You can optionally publish Lunar's migrations so they're added to your Laravel app.

```sh
php artisan vendor:publish --tag=lunar.migrations
```
