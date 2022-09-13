# Quick Start

If you want to get up and running quickly to review GetCandy, this section is for you.

[[toc]]

## Demo Store

GetCandy provides a demo store as a reference to help you build your own custom e-commerce app. The demo store is built using Laravel Livewire (as is our admin hub). You can quickly see how to load products, collections, use the cart and checkout.

If you would prefer to install GetCandy into your own Laravel application, please follow the [installation instructions](/installation).


## Requirements

- PHP ^8.0
- MySQL 5.7+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

::: warning
This demo store uses Meilisearch, for the best experience it is recommended you use this as well. If you want to use MySQL for search then you need to make sure you follow the configuration steps: [Search Configuration](https://docs.lunarphp.io/installation.html#search-configuration)
:::


## Installation

### Clone the repo

```bash
git clone --depth=1 https://github.com/lunarphp/demo-store.git
```

This will create a shallow clone of the repo, from there you would just need to remove the `.git` folder and reinitialise it to make it your own.

```bash
cd demo-store
rm -rf .git
```

Then install composer dependencies

```bash
composer install
```

### Configure the Laravel app

Copy the `.env.example` file to `.env` and make sure the details match to your install.

```bash
cp .env.example .env
```

All the relevant configuration files should be present in the repo.

### Migrate and seed.

Run the migrations

```
php artisan migrate
```

Install GetCandy

```
php artisan lunar:install
```

Seed the demo data.

```
php artisan db:seed
```

Link the storage directory

```
php artisan storage:link
```

## Finished 🥳

You are now installed! 

- You can access the storefront at `http://<yoursite>`
- You can access the admin hub at `http://<yoursite>/hub`

You can review the source code at the GitHub Repository: [https://github.com/lunarphp/demo-store](https://github.com/lunarphp/demo-store)

## Spread the Word

If you enjoy our project, please share it with others. The more developers using GetCandy the more we can put back into the project.

Get sharing on Twitter, Reddit, Medium, Dev.to, Laravel News, Slack, Discord, etc.

Go Team GetCandy! 🤟
