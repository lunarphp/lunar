# Search

[[toc]]

## Overview

Search is configured using the [Laravel Scout](https://laravel.com/docs/8.x/scout) package.  Out the box we have configured the GetCandy hub to use the community provided [mysql driver](https://github.com/yabhq/laravel-scout-mysql-driver).

Using Scout allows us to provide search out the box but also make it easy for you as the developer to customise and tailor searching to your needs. There is no longer a requirement for Elasticsearch to be installed like it was in previous versions of GetCandy.

## Initial set up

When installing GetCandy you will need to add the base config for using the MySQL driver, as outlined in the [installation steps](/{{route}}/{{version}}/installation). The MySQL driver provides basic search to get you up and running but you will likely find you want to implement something with a bit more power, such as [Meilisearch](https://www.meilisearch.com/) or [Algolia](https://www.algolia.com/).


## Configuration

By default, scout has the setting `soft_delete` set to `false`. You need to make sure this is set to `true` otherwise you will see soft deleted models appear in your search results.

If you are using meilisearch, there is some additional set up needed. We have a command ready to go which will set everything up for you, just run:

```php
php artisan getcandy:meilisearch:setup
```

### Additional drivers

If you don't plan on using MySQL, there are some other Scout drivers you can use, depending on your set up. Bear in mind these haven't tested by GetCandy and are provided as reference.

- [PostgreSQL](https://github.com/pmatseykanets/laravel-scout-postgres)
- [SQLite](https://github.com/teamtnt/laravel-scout-tntsearch-driver)
