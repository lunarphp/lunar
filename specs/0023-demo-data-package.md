# 0023 — Demo-data package

- Status: draft
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

A new monorepo sub-package **`packages/demo`** (`lunarphp/demo`, namespace `Lunar\Demo\…`) that seeds a coherent, reproducible store and is the shared fixture foundation a future demo storefront builds on. **Data only in this spec** — no storefront UI.

### A. Package shape

Mirrors the other sub-packages (`table-rate-shipping` as the template):

```
packages/demo/
├── composer.json          ← name lunarphp/demo; extra.laravel.providers; requires core (+ admin)
├── src/
│   ├── DemoServiceProvider.php          ← registers the command + config + asset path
│   ├── Console/Commands/DemoCommand.php ← `lunar:demo`
│   ├── Generators/                      ← one generator per domain (Catalogue, Customers, Orders, …)
│   └── Support/DemoContext.php          ← shared handles/ids passed between generators
├── database/
│   ├── seeders/DemoStoreSeeder.php      ← orchestrates the generators
│   └── factories/                       ← only where a core factory state is missing
├── resources/
│   └── fixtures/                        ← product copy (JSON) + placeholder images
└── config/demo.php                      ← scale knobs (see §D)
```

Wired like the others: `extra.laravel.providers` auto-discovers `DemoServiceProvider`; the package is registered in `monorepo-builder.php` and added to the root `composer.json` path repos so the host app resolves it. It splits to its own repo at the v2.0.0 cut like the rest.

### B. Entry point — `php artisan lunar:demo`

```
php artisan lunar:demo            # seed (idempotent: refuses to double-seed unless --fresh)
php artisan lunar:demo --fresh    # wipe demo-owned tables, then seed
php artisan lunar:demo --scale=small|medium|large
```

The command is the public surface; `DemoStoreSeeder` is callable from a host `DatabaseSeeder` too (`$this->call(DemoStoreSeeder::class)`). It is **deterministic** — faker is seeded from a fixed value (passed in via config, never `Math::random`-style at runtime) so the same scale always produces the same store, making screenshots and review diffs stable.

### C. What it generates

A connected graph, built in dependency order by the generators:

1. **Foundation** — currencies (GBP default + USD/EUR), tax zone + rates, a `Webstore` channel, customer groups, languages already present.
2. **Catalogue** — product types with attributes; ~N products across a few collections, each with variants, per-currency prices, and placeholder media from `resources/fixtures/`. Product copy comes from a small curated JSON fixture (real-sounding names/descriptions, not lorem) so it reads like a store.
3. **Customers** — customers with addresses, some linked to users.
4. **Orders spanning the full lifecycle** — the important part for [[0022-order-fulfilments]]. Orders are built by creating the **source records** and letting the derivation run, never by hand-setting derived columns:
   - create the order + lines,
   - attach `transactions` (intent / capture / refund) for the target money story,
   - attach `Fulfilment` + `FulfilmentLine` rows (via the `Fulfilments` facade) and transition them,
   - let the recompute observers settle `payment_status` / `fulfilment_status` / `status`.

   The set covers every derived headline and both manual overrides: `awaiting-payment`, `in-process`, `partially-shipped`, `shipped`, `refunded`, plus `on-hold` and `cancelled`. This is the fixture the 0022 §K relation-manager + badge work verifies against.
5. **Carts (optional)** — a few open carts for cart/checkout work.

Each generator is independently callable and idempotent against its own data, so partial reseeds are cheap.

### D. Config — `config/demo.php`

Documented knobs: the faker seed; per-scale counts (`small` ≈ a dozen products / handful of orders for fast local use, up to `large` for stress/screenshots); which channels/currencies to include; an `asset_disk` for placeholder media. No secrets.

### E. Boundaries

- **Dev/showcase tool, not a stability contract.** `Lunar\Demo\…` is explicitly *not* part of the public contract surface — generators and seeders may change shape between versions without a Rector rule. Documented in the package README.
- **Depends inward only.** `lunarphp/demo` requires `core` (and `admin` for any panel-facing niceties); nothing in `core`/`admin`/`filament` ever depends on `demo`. The stub `core` `DemoSeeder` is deleted or reduced to the bare channel/product-type bootstrap the test harness needs — the rich demo lives here.
- **Not auto-installed in production.** Seeding is an explicit command; the package ships but does nothing until `lunar:demo` is run.

## Alternatives considered

- **Enrich the existing `core` `DemoSeeder`.** Rejected: pulls demo copy, placeholder images, and order/fulfilment fixtures into `core`, bloating the package every consumer ships and blurring the "core is the commerce engine" boundary. Demo data is dev/showcase content.
- **Standalone `lunarphp/demo-store` repo (the v1 model).** Rejected *for now*: a full storefront app is a much larger effort and a separate decision. A monorepo data package is lighter, ships with the same release cadence, and is the foundation a storefront would consume later — so this doesn't preclude it.
- **Keep using per-PR tinker snippets.** Rejected: not reproducible, not shareable, and re-derived every time (the pain in §Problem).
- **A faker-only inline seeder with no fixtures.** Rejected: lorem-ipsum products don't read like a store and make screenshots/demos look unfinished; a small curated copy fixture is cheap and far more convincing.

## Migration impact

- **Database migrations:** none. The package only writes through existing models/migrations.
- **Dependencies / structure:** adds a new sub-package and a new base folder — needs sign-off per the monorepo rules. Adds `lunarphp/demo` to `monorepo-builder.php` and the root path repos. No new runtime dependency for consumers who don't install it.
- **Breaking changes to the public contract surface:** none. `Lunar\Demo\…` is declared non-contract (§E). Removing/shrinking the `core` `DemoSeeder` is an internal-seeder change; if any downstream calls it, note it in the upgrade guide (it is not a documented seam).
- **Translation / locale impact (16 locales):** demo *content* (product copy, store name) is English-only and explicitly out of the 16-locale contract. Command-output strings are dev-facing and need not be localised. No `lunar::`/`lunar-filament::` keys added.
- **Filament / admin impact:** none structural — seeded records simply populate the existing resources, which is the point. Lets 0022 §K (and future admin work) be verified against a realistic dataset.

## Open questions

- **Package vs. command-in-core.** Proposal favours a dedicated package; confirm before allocating the package slot.
- **Demo storefront.** Out of scope here (data only). Decide separately whether a v2 storefront app follows, consuming this package's data.
- **Asset strategy.** Bundle a small set of placeholder/product images in `resources/fixtures/`, or generate solid-colour placeholders at runtime to keep the package lean? (Leaning: a handful of bundled images, generated fallback if the disk is unavailable.)
- **Scale defaults.** What counts as `small` / `medium` / `large`, and which is the default for a bare `lunar:demo`?
- **Order realism.** How far to push the order set — just enough to cover every derived state once, or a larger, more natural distribution (repeat customers, mixed baskets, partial refunds) for screenshots?

## References

- [[0022-order-fulfilments]] — the fulfilment/derived-status work this package's order fixtures exercise (§K verification).
- [[0006-filament-bridge-package]] — precedent for adding and wiring a monorepo sub-package.
- `packages/core/database/seeders/DemoSeeder.php` — the current stub this replaces.
- `packages/table-rate-shipping/` — sub-package layout / `composer.json` template.
- Lunar v1 `lunarphp/demo-store` — prior art for a demo store.
