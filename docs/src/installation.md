# Installation

[[toc]]

## Requirements

- PHP ^8.1
- Laravel 9|10
- MySQL 5.7+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- intl PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Install Lunar

### Composer Require Package

```sh
composer require lunarphp/lunar
```

### Add the `LunarUser` Trait

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

### Add Scout engine to ENV
Add the following line to your `.env` file.
```
SCOUT_DRIVER=database_index
```

### Run the Artisan Installer

```sh
php artisan lunar:install
```

This will take you through a set of questions to configure your Lunar install. The process includes...

- Creating a default admin user (if required)
- Seeding initial data
- Inviting you to star our repo on GitHub ⭐

::: tip Success 🎉
You are now installed! You can access the admin hub at `http://<yoursite>/hub`
:::

## Advanced Installation Options

Before you run the Lunar installer command, you may wish to customise some of the set-up.

### Publish Configuration
```sh
php artisan vendor:publish --tag=lunar
```

### Table Prefix
Lunar uses table prefixes to avoid conflicts with your app's tables. You can change this in the [configuration](/configuration.html).


### User ID Field Type
Lunar assumes your User ID field is a "BIGINT". If you are using an "INT" or "UUID", you will want to update the configuration in `config/lunar/database.php` to set the correct field type before running the migrations.


### Publish Migrations
You can optionally publish Lunar's migrations so they're added to your Laravel app.

```sh
php artisan vendor:publish --tag=lunar.migrations
```
