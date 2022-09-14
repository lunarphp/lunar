# Search

[[toc]]

## Overview

Search is configured using the [Laravel Scout](https://laravel.com/docs/8.x/scout) package.  Out the box we have configured the Lunar hub to use the community provided [mysql driver](https://github.com/yabhq/laravel-scout-mysql-driver).

Using Scout allows us to provide search out the box but also make it easy for you as the developer to customise and tailor searching to your needs. There is no longer a requirement for Elasticsearch to be installed like it was in previous versions of Lunar.

## Initial set up

When installing Lunar you will need to add the base config for using the MySQL driver, as outlined in the [installation steps](/{{route}}/{{version}}/installation). The MySQL driver provides basic search to get you up and running but you will likely find you want to implement something with a bit more power, such as [Meilisearch](https://www.meilisearch.com/) or [Algolia](https://www.algolia.com/).


## Configuration

By default, scout has the setting `soft_delete` set to `false`. You need to make sure this is set to `true` otherwise you will see soft deleted models appear in your search results.

If you are using meilisearch, there is some additional set up needed. We have a command ready to go which will set everything up for you, just run:

```php
php artisan lunar:meilisearch:setup
```

The above command will create the indexes for the models listed in the config file `lunar/search.php`. If you want to use other models or your own models in the search engine, you can add the reference for them on the config file.

```php
'models' => [
        // These models are required by the system, do not change them.
        \Lunar\Models\Collection::class,
        \Lunar\Models\Product::class,
        \Lunar\Models\ProductOption::class,
        \Lunar\Models\Order::class,
        \Lunar\Models\Customer::class,
        // Below you can add your own models for indexing
    ]
```

### Additional drivers

If you don't plan on using MySQL, there are some other Scout drivers you can use, depending on your set up. Bear in mind these haven't tested by Lunar and are provided as reference.

- [PostgreSQL](https://github.com/pmatseykanets/laravel-scout-postgres)
- [SQLite](https://github.com/teamtnt/laravel-scout-tntsearch-driver)

## Index records

If you installed the Lunar package in an existing project and you like to use the database records with the search engine, or you just need to do some maintenance on the indexes, you can use the index command.

```sh
php artisan lunar:search:index
```

The command will import the records of the models listed in the `lunar/indexer.php` configuration file. Type `--help` to see the available options.

## Engine Mapping

By default, Scout will use the driver defined in your .env file as `SCOUT_DRIVER`. So if that's set to `meilisearch`, all your models will be indexed via the Meilisearch driver. This can present some issues, if you wanted to use a service like Algolia for Products, you wouldn't want all your Orders being indexed there since it will ramp up the record count and the cost.

In Lunar we've made it possible to define what driver you would like to use per model. It's all defined in the `config/lunar/search.php` config file and looks like this:

```php
'engine_map' => [
    \Lunar\Models\Product::class => 'algolia',
    \Lunar\Models\Order::class => 'meilisearch',
    \Lunar\Models\Collection::class => 'meilisearch',
],
```

It's quite self explanatory, if a model class isn't added to the config, it will take on the Scout default.