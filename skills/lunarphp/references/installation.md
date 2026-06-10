# Installation

> This reference covers post-install configuration and common setup tasks.
> For first-time installation, follow the [official installation guide](https://docs.lunarphp.com/1.x/getting-started/setup/installation.md).

## Requirements

- PHP >= 8.3
- Laravel 12 or 13
- MySQL 8.0+ / PostgreSQL 9.4+
- `bcmath`, `exif`, `intl` PHP extensions

## Post-Install Configuration

Publish configs before installing to customize settings:

```bash
php artisan vendor:publish --tag=lunar
```

Key config values to check:

```php
// config/lunar/database.php
'table_prefix' => 'lunar_',
'users_id_type' => 'bigint', // or 'int', 'uuid'

// config/lunar/cart_session.php
'session_key' => 'lunar_cart',
'auto_create' => false,

// config/lunar/payments.php
'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),
```

## Key Artisan Commands

| Command | Purpose |
|---------|---------|
| `lunar:install` | Run the Lunar installer |
| `lunar:search:index` | Import/refresh search indexes |
| `lunar:meilisearch:setup` | Configure Meilisearch filterable/sortable fields |
| `lunar:update` | Handle Lunar updates and migrations |
| `vendor:publish --tag=lunar` | Publish Lunar configs |
| `vendor:publish --tag=lunarpanel.pdf` | Publish order PDF template |

## Starter Kits

For quick storefront setup, use one of the official starter kits:

- [Livewire Starter Kit](https://docs.lunarphp.com/1.x/getting-started/starter-kits/livewire.md)
- [Inertia + Vue Starter Kit](https://docs.lunarphp.com/1.x/getting-starter-kits/inertia-vue.md)
