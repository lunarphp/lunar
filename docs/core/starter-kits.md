# Starter Kits

## Introduction

If you want to get up and running quickly to review Lunar, this section is for you.

## Livewire Starter Kit

Lunar provides a starter kit as a reference to help you build your own custom e-commerce app. The starter kit is built 
using Laravel Livewire (as is our admin hub). You can quickly see how to load products, collections, use the cart and 
checkout.

If you would prefer to install Lunar into your own Laravel application, please follow the 
[installation instructions](/core/installation).

::: info
The starter kit is just that, a starting point for your project. It is **not** a comprehensive storefront and does not
implement all functionality. It is up to you to develop the functionality you require for your project.
:::

## Requirements

- PHP >= 8.2
- MySQL 8.0+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- intl PHP extension (on most systems it will be installed by default)
- bcmath PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Installation

::: tip
We assume you have a suitable local development environment in which to run Lunar. We would highly suggest Laravel Herd 
for this purpose.
:::

### Create a New Project

```bash
composer create-project --stability dev lunarphp/livewire-starter-kit example-store

cd example-store
```

### Configure The Laravel App

Copy the `.env.example` file to `.env` and make sure the details match to your install.

```bash
cp .env.example .env
```

All the relevant configuration files should be present in the repo.

### Migrate and Seed

Install Lunar

```bash
php artisan lunar:install
```

Seed the demo data.

```bash
php artisan db:seed
```

Link the storage directory

```bash
php artisan storage:link
```

## Finished 🚀

You are now installed!

- You can access the storefront at `http://<yoursite>`
- You can access the admin hub at `http://<yoursite>/lunar`

You can review the source code at the GitHub Repository: [https://github.com/lunarphp/livewire-starter-kit](https://github.com/lunarphp/livewire-starter-kit).
