# Lunar Demo Data

Seeds a coherent, reproducible demo store — catalogue, customers, and orders
spanning the full order lifecycle — for evaluation, screenshots, and admin
verification. Data only; there is no storefront UI.

> This is a **development/showcase** tool, not part of Lunar's public contract
> surface. Its generators and seeders may change shape between versions without
> a Rector rule or upgrade path.

## Install

It is a **dev-only** dependency — install it under `require-dev` so a
production `composer install --no-dev` omits it entirely:

```bash
composer require --dev lunarphp/demo-data
```

## Usage

```bash
php artisan lunar:demo-data            # seed at the default scale
php artisan lunar:demo-data --scale=medium
php artisan lunar:demo-data --fresh    # wipe demo-owned tables, then seed
php artisan lunar:demo-data --force    # required to run in a production environment
```

The command refuses to run in production without `--force`, mirroring Laravel's
own `migrate:fresh` / `db:wipe`. Seeding is deterministic — faker is seeded from
a fixed value (see `config/demo-data.php`) so the same scale always produces the
same store.

`DemoDataSeeder` is also callable from a host `DatabaseSeeder`:

```php
$this->call(\Lunar\DemoData\Database\Seeders\DemoDataSeeder::class);
```

## Configuration

Publish the config to tune the faker seed, per-scale volumes, currencies, and
the media disk:

```bash
php artisan vendor:publish --tag=lunar.demo-data
```
