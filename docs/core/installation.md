# Installation

::: danger Alpha Release
Although many Lunar sites have been launched using v1.x, you may not consider this version production-ready for your own
use and should exercise the same amount of caution as you would with any software in an alpha state. 🚀
:::

## Requirements

- PHP >= 8.2
- Laravel 10 / Laravel 11
- MySQL 8.0+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- intl PHP extension (on most systems it will be installed by default)
- bcmath PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Install Lunar

### Composer Require Package

```sh
composer require lunarphp/lunar:"^1.0.0-alpha" -W
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

### Publish Configuration
Before you run the Lunar installer command, you may wish to customise some of the set-up.


```sh
php artisan vendor:publish --tag=lunar
```

## Configure Laravel Scout
Lunar works best with [Laravel Scout](https://laravel.com/docs/master/scout) and a search engine like Meilisearch, Typesense or Algolia.

### If you do NOT have a search engine configured
Add the following to your `.env` file.
```
SCOUT_DRIVER=null
```
And set the config value in `panel.php` as follows.
```php
    'scout_enabled' => false,
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

## Run the Artisan Installer

```sh
php artisan lunar:install
```

This will take you through a set of questions to configure your Lunar install. The process includes...

- Creating a default admin user (if required)
- Seeding initial data
- Inviting you to star our repo on GitHub ⭐

You should now be able to access the panel at `https://<yoursite>/lunar`.

---

## Advanced Installation Options

### Table Prefix

Lunar uses table prefixes to avoid conflicts with your app's tables. You can change this in the [configuration](/core/configuration.html).

### User ID Field Type

Lunar assumes your User ID field is a "BIGINT". If you are using an "INT" or "UUID", you will want to update the configuration in `config/lunar/database.php` to set the correct field type before running the migrations.

### Publish Migrations

You can optionally publish Lunar's migrations so they're added to your Laravel app.

```sh
php artisan vendor:publish --tag=lunar.migrations
```
