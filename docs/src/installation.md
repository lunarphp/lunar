# Installation

[[toc]]

## Requirements

- PHP ^8.0
- Laravel 8|9
- MySQL 5.7+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- intl PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Install GetCandy

::: warning Beta Software
GetCandy is currently in Public Beta. The software may not be stable enough for a production site and further beta releases may bring breaking changes.
:::

### Composer Require Package

```sh
composer require lunar/admin
```

### Publish the Config Files

```sh
php artisan vendor:publish --tag=lunar
```

## Add the `GetCandyUser` Trait

Some parts of the core rely on the `User` model having certain relationships set up. We've bundled these into a trait which you must add to any models that represent users in your database.

```php
use Lunar\Base\Traits\GetCandyUser;
// ...

class User extends Authenticatable
{
    use GetCandyUser;
    // ...
}
```

## Search Configuration

GetCandy uses Laravel Scout for search. We have had good success using Meilisearch, although it's entirely up to you which driver you use, as long as it's compatible.

If you just want to give the wheels a spin, we also ship with a MySQL driver. Just bear in mind this is highly restrictive and we do not recommend using this in any production capacity.

Publish the Scout config.

```sh
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

By default, scout has the setting `soft_delete` set to false. You need to make sure this is set to true otherwise you will see soft deleted models appear in your search results.

### Going with Meilisearch

::: tip Recommended
Meilisearch is the recommended search driver for GetCandy.
:::

If you're on OSX then you can use [Takeout](https://github.com/tighten/takeout) which makes installing Meilisearch via Docker a breeze.

Meilisearch also provide great documentation on how to get set up.

[Install Meilisearch](https://docs.meilisearch.com/learn/getting_started/installation.html)

Once you have Meilisearch up and running, simply require the composer packages.

```sh
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
```

Add/update the entry in your `.env` file as follows, changing the host and key as required.

```
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=masterKey
```

See the [Laravel Scout documentation](https://laravel.com/docs/8.x/scout#meilisearch) for more information.

### Going with MySQL

::: warning Development Only
We suggest the MySQL driver is only used for development purposes.
:::

Add/update the entry in your `.env` file as follows.

```
SCOUT_DRIVER=mysql
```

Then finally, add this to your `scout.php` config file.

```php
/*
|--------------------------------------------------------------------------
| MySQL Configuration
|--------------------------------------------------------------------------
*/
'mysql' => [
    'mode' => 'LIKE_EXPANDED',
    'model_directories' => [app_path()],
    'min_search_length' => 0,
    'min_fulltext_search_length' => 4,
    'min_fulltext_search_fallback' => 'LIKE',
    'query_expansion' => false
],
```

## Admin Hub

### Publish Assets

The admin hub requires some assets to work. Run the following command to publish them to your public directory.

```sh
php artisan lunar:hub:install
```

## Run Migrations

::: tip
GetCandy uses table prefixes to avoid conflicts with your app's tables. You can change this in the [configuration](/configuration.html).
:::

::: warning
GetCandy assumes your User ID field is a "BIGINT". If you are using an "INT" or "UUID", you will want to update the configuration in `config/lunar/database.php` to set the correct field type before running the migrations.
:::

As you'd expect, there's quite a few tables Lunar needs to function, so run the migrations now.

You can optionally publish these migrations so they're added to your Laravel app.

```sh
php artisan vendor:publish --tag=lunar-migrations
```

```sh
php artisan migrate
```

## Run the Artisan Installer

```sh
php artisan lunar:install
```

This will take you through a set of questions to configure your Lunar install. The process includes...

- Creating a default admin user (if required)
- Seeding initial data
- Inviting you to star our repo on GitHub ⭐

## Final Meilisearch Set-up

If you are using Meilisearch, you just need to do some final configuration. Simply run this command.

```sh
php artisan lunar:meilisearch:setup
```

::: tip Success 🎉
You are now installed! You can access the admin hub at `http://<yoursite>/hub`
:::

## Spread the Word

If you enjoy our project, please share it with others. The more developers using Lunar the more we can put back into the project.

Get sharing on Twitter, Reddit, Medium, Dev.to, Laravel News, Slack, Discord, etc.

Go Team GetCandy! 🤟
