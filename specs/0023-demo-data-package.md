# 0023 — Demo-data package

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-06-05
- TODO item: "Demo-data package — seed a realistic store (products, orders, fulfilments) for evaluation, screenshots, and admin/storefront verification"

## Problem

There is no way to stand up a realistic Lunar v2 system to look at. The only seeder that ships is `packages/core/database/seeders/DemoSeeder.php`, and it is a stub — it truncates four tables and creates **one** channel and **one** product type with a single attribute. No products, variants, prices, media, collections, customers, carts, orders, transactions, or fulfilments. `TestingSeeder` is similarly minimal and exists for the test boot, not for a human.

That gap costs us in three concrete ways:

- **Features can't be eyeballed.** Verifying [[0022-order-fulfilments]]'s §K admin work (the three status badges, the fulfilments relation manager, the derived `payment_status` / `fulfilment_status`) needs orders that actually span the lifecycle — with real `transactions` and `Fulfilment` rows so the derivation has something to roll up. Today a reviewer has to hand-write tinker scripts to see anything, which is exactly what happened verifying 0022. Every future order/cart/pricing/discount feature has the same problem.
- **No demo store.** v1 shipped a separate `lunarphp/demo-store` so people could try Lunar without building a catalogue first. v2 has no equivalent. Evaluation, screenshots, docs, and conference demos all start from an empty database.
- **Fixtures are reinvented per author.** Each PR that needs sample data grows its own throwaway seeding, none of it reusable or reproducible.

## Proposal

A new monorepo sub-package **`packages/demo-data`** (`lunarphp/demo-data`, namespace `Lunar\DemoData\…`) that seeds a coherent, reproducible store and is the shared fixture foundation a future demo storefront builds on. The name is deliberately `demo-data`, not `demo`, so it can't be mistaken for a demo storefront app — this is **data only**, no storefront UI.

### A. Package shape

Mirrors the other sub-packages (`table-rate-shipping` as the template):

```
packages/demo-data/
├── composer.json          ← name lunarphp/demo-data; extra.laravel.providers; requires core (+ admin)
├── src/
│   ├── DemoDataServiceProvider.php      ← registers the command + config + asset path
│   ├── Console/Commands/DemoCommand.php ← `lunar:demo-data`
│   ├── Generators/                      ← one generator per domain (Catalogue, Customers, Orders, …)
│   └── Support/DemoContext.php          ← shared handles/ids passed between generators
├── database/
│   ├── seeders/DemoDataSeeder.php      ← orchestrates the generators
│   └── factories/                       ← only where a core factory state is missing
├── resources/
│   └── fixtures/                        ← product copy (JSON) + placeholder images
└── config/demo-data.php                      ← scale knobs (see §D)
```

Wired like the others: `extra.laravel.providers` auto-discovers `DemoDataServiceProvider`; the package is registered in `monorepo-builder.php` and added to the root `composer.json` path repos so the host app resolves it. It splits to its own repo at the v2.0.0 cut like the rest. The command is `lunar:demo-data`, matching the package name.

### B. Entry point — `php artisan lunar:demo-data`

```
php artisan lunar:demo-data            # seed (idempotent: refuses to double-seed unless --fresh)
php artisan lunar:demo-data --fresh    # wipe demo-owned tables, then seed
php artisan lunar:demo-data --scale=small|medium|large
php artisan lunar:demo-data --force    # required to run in a production environment
```

The command is the public surface; `DemoDataSeeder` is callable from a host `DatabaseSeeder` too (`$this->call(DemoDataSeeder::class)`). It is **deterministic** — faker is seeded from a fixed value (passed in via config, never `Math::random`-style at runtime) so the same scale always produces the same store, making screenshots and review diffs stable.

### C. What it generates

A connected graph, built in dependency order by the generators:

1. **Foundation** — currencies (GBP default + USD/EUR), tax zone + rates, a `Webstore` channel, customer groups, languages already present, and one or more `Location`s (stock is per-location since [[0038-inventory-fundamentals]], so at least a default location must exist before any variant stock can be seeded).
2. **Catalogue** — product types with attributes; ~N products across a few collections, each with variants, per-currency prices, and placeholder media from `resources/fixtures/`. Product copy comes from a small curated JSON fixture (real-sounding names/descriptions, not lorem) so it reads like a store. **Stock** is seeded through the [[0038-inventory-fundamentals]] seam — `$variant->adjustStock($location, $qty, StockMovementType::OpeningBalance)` per variant per location — never by writing the `stock_*` rollup columns directly; the recompute settles `stock_on_hand` / `stock_available`. Leave a few variants at zero / low stock so out-of-stock and low-stock states are visible.
3. **Customers** — customers with addresses, some linked to users.
4. **Orders spanning the full lifecycle** — the important part for [[0022-order-fulfilments]]. There is no single headline `status` any more: an order's headline is **two derived rollups** plus an open/closed/cancelled lifecycle. Orders are built by creating the **source records** and letting the derivation run, never by hand-setting derived columns:
   - create the order + lines,
   - attach `transactions` (intent / capture / refund) for the target money story,
   - create `Fulfilment` + `FulfilmentLine` rows via `$order->createFulfilment($lines, $attributes)` (where `$attributes` carries the `method` key) and drive them with the fulfilment verbs — `ship($tracking)` for the `shipping` method, `fulfil()` for `collection`/`digital`, plus `markReturned()`, `cancel()`, `hold()` / `release()`,
   - let the recompute observers settle `payment_status` and `fulfilment_status`; `closed_at` / `cancelled_at` come from `close()` / `cancel()`.

   The set covers, across the two axes:
   - **`payment_status`**: `Pending`, `Authorized`, `PartiallyPaid`, `Paid`, `PartiallyRefunded`, `Refunded`, `Voided`.
   - **`fulfilment_status`**: `Unfulfilled`, `PartiallyFulfilled`, `Fulfilled`, `PartiallyReturned`, `Returned`.
   - **lifecycle**: open (default), `cancelled` (`cancel()`), and closed (`close()`).

   It also exercises all three [[0031-fulfilment-methods]] method drivers so the method-aware modals/badges have data: a **shipping** order (tracking, `Pending → Shipped`, plus one un-shipped / returned), a **collection** order (`Pending → ReadyForCollection → Collected`, no tracking), and a **digital** order (`Pending → Provisioned`). At least one multi-parcel order (partial fulfilment + partial payment) and one on-hold fulfilment. This is the fixture the 0022 §K relation-manager + badge work verifies against.
5. **Carts (optional)** — a few open carts for cart/checkout work.

Each generator is independently callable and idempotent against its own data, so partial reseeds are cheap.

### D. Config — `config/demo-data.php`

Documented knobs: the faker seed; per-scale counts; which channels/currencies to include; an `asset_disk` for placeholder media. No secrets.

A bare `lunar:demo-data` defaults to `small` (fastest turnaround); `--scale=medium|large` opt into larger sets. Indicative per-scale counts:

| scale  | products | variants | collections | customers | orders |
| ------ | -------- | -------- | ----------- | --------- | ------ |
| small  | ~12      | ~25      | 3           | ~8        | ~10    |
| medium | ~50      | ~120     | 6           | ~40       | ~50    |
| large  | ~250     | ~600     | 12          | ~200      | ~250   |

Regardless of scale the order set always covers every `payment_status` x `fulfilment_status` combination plus all three fulfilment methods at least once (see §C.4); larger scales layer a natural distribution (repeat customers, mixed baskets, partial refunds) on top of that guaranteed coverage.

### E. Boundaries

- **Dev/showcase tool, not a stability contract.** `Lunar\DemoData\…` is explicitly *not* part of the public contract surface — generators and seeders may change shape between versions without a Rector rule. Documented in the package README.
- **Depends inward only.** `lunarphp/demo-data` requires `core` (and `admin` for any panel-facing niceties); nothing in `core`/`admin`/`filament` ever depends on `demo-data`. The stub `core` `DemoSeeder` is deleted or reduced to the bare channel/product-type bootstrap the test harness needs — the rich demo lives here.
- **A dev dependency, not a production one.** The package is installed via `composer require --dev lunarphp/demo-data`, so a production `composer install --no-dev` omits it entirely — the provider, command and seeder are simply absent (Laravel's package-discovery manifest is regenerated without it; nothing in `core`/`admin`/`filament` references it, per "depends inward only"). The README documents the `--dev` install.
- **Guarded against production runs.** Composer can't *enforce* that a consumer keeps it in `require-dev`, and `--fresh` is destructive, so the command itself refuses to run when `app()->isProduction()` unless `--force` is passed — mirroring Laravel's own `migrate:fresh` / `db:wipe`.

## Alternatives considered

- **Enrich the existing `core` `DemoSeeder`.** Rejected: pulls demo copy, placeholder images, and order/fulfilment fixtures into `core`, bloating the package every consumer ships and blurring the "core is the commerce engine" boundary. Demo data is dev/showcase content.
- **Standalone `lunarphp/demo-store` repo (the v1 model).** Rejected *for now*: a full storefront app is a much larger effort and a separate decision. A monorepo data package is lighter, ships with the same release cadence, and is the foundation a storefront would consume later — so this doesn't preclude it.
- **Keep using per-PR tinker snippets.** Rejected: not reproducible, not shareable, and re-derived every time (the pain in §Problem).
- **A faker-only inline seeder with no fixtures.** Rejected: lorem-ipsum products don't read like a store and make screenshots/demos look unfinished; a small curated copy fixture is cheap and far more convincing.

## Migration impact

- **Database migrations:** none. The package only writes through existing models/migrations.
- **Dependencies / structure:** adds a new sub-package and a new base folder — needs sign-off per the monorepo rules. Adds `lunarphp/demo-data` to `monorepo-builder.php` and the root path repos. It is a **dev-only** dependency for consumers (`require-dev`), so it adds no production runtime dependency; the monorepo's own host app likewise lists it under `require-dev`.
- **Breaking changes to the public contract surface:** none. `Lunar\DemoData\…` is declared non-contract (§E). Removing/shrinking the `core` `DemoSeeder` is an internal-seeder change; if any downstream calls it, note it in the upgrade guide (it is not a documented seam).
- **Translation / locale impact (16 locales):** demo *content* (product copy, store name) is English-only and explicitly out of the 16-locale contract. Command-output strings are dev-facing and need not be localised. No `lunar::`/`lunar-filament::` keys added.
- **Filament / admin impact:** none structural — seeded records simply populate the existing resources, which is the point. Lets 0022 §K (and future admin work) be verified against a realistic dataset.

## Open questions

- ~~**Package vs. command-in-core.**~~ **Resolved:** dedicated sub-package, named `lunarphp/demo-data` (not `demo`) so it can't be read as a demo storefront; command `lunar:demo-data` to match.
- **Demo storefront.** Out of scope here (data only); a v2 storefront app, if it follows, is a separate decision that consumes this package's data. The `demo-data` name keeps that door open without implying it.
- ~~**Asset strategy.**~~ **Resolved:** ship a curated set of real placeholder/product images bundled in `resources/fixtures/` (reused across products); no runtime-generated fallback. Demo media should look like a real store; consumers must point `asset_disk` at a writable disk.
- ~~**Scale defaults.**~~ **Resolved:** counts table in §D; bare `lunar:demo-data` defaults to `small`.
- ~~**Order realism.**~~ **Resolved:** every scale guarantees full `payment_status` x `fulfilment_status` + 3-method coverage; medium/large layer a natural distribution (repeat customers, mixed baskets, varied currencies, partial refunds/returns) on top (§C.4, §D).

## References

- [[0022-order-fulfilments]] — the fulfilment/derived-status work this package's order fixtures exercise (§K verification).
- [[0031-fulfilment-methods]] — the shipping / collection / digital method drivers the order fixtures span.
- [[0038-inventory-fundamentals]] — per-location stock; variant stock is seeded through its `adjustStock(...)` seam.
- [[0006-filament-bridge-package]] — precedent for adding and wiring a monorepo sub-package.
- `packages/core/database/seeders/DemoSeeder.php` — the current stub this replaces.
- `packages/table-rate-shipping/` — sub-package layout / `composer.json` template.
- Lunar v1 `lunarphp/demo-store` — prior art for a demo store.

## Implementation plan

Additive throughout — the package does nothing until `lunar:demo-data` runs, so each slice lands with the app working. The generators are built in dependency order; later slices are independently reviewable because each generator is callable and idempotent on its own.

- [ ] **Slice 1 — package scaffold.** Create `packages/demo-data` (`lunarphp/demo-data`, `Lunar\DemoData\…`) mirroring `table-rate-shipping`: `DemoDataServiceProvider`, `config/demo-data.php` (faker seed + per-scale counts + channels/currencies + `asset_disk`), the `lunar:demo-data` command (`--fresh`, `--scale`, `--force` with an `isProduction()` guard), `DemoContext`, and an empty `DemoDataSeeder` orchestrator. Register in `monorepo-builder.php`, root `composer.json` path repos + `extra.laravel.providers`; the monorepo host app requires it under `require-dev`, and the README documents the `composer require --dev` install. Reduce the `core` `DemoSeeder` to the bare channel/product-type bootstrap the test harness needs (note in upgrade guide).
- [ ] **Slice 2 — foundation generator.** Currencies (GBP default + USD/EUR), tax zone + rates, `Webstore` channel, customer groups, and a default `Location`. Idempotent against its own data.
- [ ] **Slice 3 — catalogue generator.** Product types + attributes; products/variants/per-currency prices/collections from a curated JSON copy fixture; bundled placeholder media from `resources/fixtures/`; stock seeded via `$variant->adjustStock($location, $qty, StockMovementType::OpeningBalance)` with some zero/low-stock variants.
- [ ] **Slice 4 — customers generator.** Customers with addresses, some linked to users.
- [ ] **Slice 5 — orders generator.** The verification fixture: build source records (lines, transactions, `createFulfilment` + verbs) and let derivation settle. Guarantees full `payment_status` x `fulfilment_status` coverage + all three fulfilment methods + multi-parcel / on-hold / cancelled / closed at every scale; layers the natural distribution (repeat customers, mixed baskets, varied currencies, partial refunds/returns) on top at medium/large. Optional open carts.
- [ ] **Slice 6 — scale + determinism polish.** Wire the three scale presets to the count knobs, fix the faker seed for reproducible output, and harden `--fresh` / double-seed idempotency. Feature test asserting a `small` seed produces every derived state and all three methods.
