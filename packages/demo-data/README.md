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
own `migrate:fresh` / `db:wipe`. Without `--fresh` it is a no-op once the store
is seeded; use `--fresh` to wipe the demo-owned tables and rebuild.

Seeding is reproducible at the structural level — the same scale always yields
the same catalogue, the same order coverage, and the same counts (faker is
seeded from a fixed value in `config/demo-data.php`). Cosmetic faker output
(customer names, addresses) is best-effort: the framework draws from the global
RNG stream mid-seed, so those values are not byte-identical between runs.

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
