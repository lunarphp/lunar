# What's new in Lunar v2

Lunar v2 is a ground-up modernisation of the headless commerce core. This is a
short tour of the headline changes and why they matter. It is still in active
development and the API is unstable — expect breaking changes between alphas.

Full design detail for every item lives in `specs/` (one file per change);
`TODO.md` tracks what is shipped and what is still to come.

## Requirements

- PHP 8.4+
- Laravel 12 or 13
- Filament v5 (admin panel) / Livewire v4

## Seeing it in action

A new `lunarphp/demo-data` package seeds a coherent, reproducible store — products,
variants, prices, collections, customers, and orders that span the full
fulfilment and payment lifecycle — so you can stand up a realistic system to
click around rather than starting from an empty database.

Install Lunar into your Laravel app first, then seed:

```
php artisan lunar:install                     # install Lunar (migrations, config, panel)
php artisan lunar:demo-data                    # seed demo data
php artisan lunar:demo-data --fresh            # wipe demo data, then seed
php artisan lunar:demo-data --scale=small|medium|large
```

## Extending Lunar

**What changed.** Model class substitution is gone. You no longer swap a Lunar
model by binding your own subclass — `HasModelExtending`, `ModelManifest`
substitution, the `Models\Contracts\*` interfaces and `Model::modelClass()` have
all been removed. Lunar models are now single-identity records.

**How you extend now.** Use the native Laravel seams you already know:
`resolveRelationUsing()`, macros, `addGlobalScope()`, `observe()`, and container
bindings for behaviour. Two Lunar seams fill the gaps: `Model::addCasts()`
registers casts/accessors for columns you add, and `Model::addLocalScope()`
registers query scopes (native scopes win on collision).

**Why it's better.** No fragile subclass hierarchy to keep in sync with core, no
guessing which class is "the real one", and customisation reads like ordinary
Laravel. A Rector rule in the upgrade package migrates old `modelClass()` calls.

## Every operation is an action, every action has a verb

Service-layer classes now declare their collaborators via constructor injection
and bind to interfaces in `Contracts/`, so you swap an implementation by binding
its interface — no config-string class swaps. Each operation is an action class
(`execute()`), surfaced as a verb on the model it operates on: `$cart->createOrder()`,
`$fulfilment->ship($tracking)`. You get a clear, testable seam for every
behaviour and an ergonomic call site that doesn't reach into the container.

## Orders, fulfilment & lifecycle

Order status is no longer a hand-set string. It splits into two derived state
machines (`spatie/laravel-model-states`) cast on the order — `payment_status`
and `fulfilment_status` — rolled up from the order's transactions and
fulfilments rather than set by hand. Fulfilment itself is modelled as per-parcel
`Fulfilment` records with their own guarded lifecycle, backed by pluggable
fulfilment methods (`shipping`, `collection`, `digital` ship by default) and a
shipping-carrier registry with tracking; the `FulfilmentStateConfig` seam
reshapes that lifecycle. Orders open and close (`closed_at`) instead of carrying
a manual status, and can auto-close once fully paid and fulfilled. The upshot:
order handling is transition-safe and models real multi-parcel, multi-method
fulfilment out of the box.

## Inventory & stock

Stock is now per-location: `StockLevel`, `StockMovement` and `StockReservation`
sit on top of `Location`, with global rollups denormalised onto `ProductVariant`
and all physical movement flowing through `$variant->adjustStock(...)`. The old
flat `stock` column is gone. The stringly-typed `product_variants.purchasable`
mode is renamed to `selling_policy` with a typed `SellingPolicy` enum. You get a
real inventory foundation to build reservations, routing and reporting on.

## Storefront context & regions

`Region` is now a first-class market — it belongs to a channel and carries
currency, language, tax zone, served countries and a price-display preference —
and is stamped on carts and orders. A single `ResolveStorefrontContext` cascade
produces an immutable `StorefrontContext` DTO (channel, currency, language,
region, customer, groups), so non-session code (APIs, jobs, tests) works from an
explicit context instead of reaching into session state.

## Customer notifications

Order notifications are configured through an `OrderNotifications` manifest
(each entry carries its auto-triggers, a manual/resendable flag and a scope)
rather than a config map. An interactive "Notify customer" order action lets
staff pick a notification, add a message, choose recipients and record the send.
Fulfilment events gate on a "notify customer" toggle. (Branded default templates
for the full lifecycle are still to come.)

## Caching & events

Lunar emits semantic, per-entity invalidation events after commit, deduped and
tag-based so references never fan out unbounded — and search reindexing rides
the same events, closing the programmatic-change gap. On the read side,
`CacheTags::for($model)` resolves the deduped set of tags a page depends on from
a registry of named dependency graphs. This gives storefronts a precise cache
key/invalidation vocabulary without hand-rolled busting.

## Pricing & attributes

Prices are stored as plain integers with a `FormatsPrices` trait, and a new
`PriceValue` object plus `PriceCalculator` centralise currency-aware arithmetic
(rounding, tax, distribution) so money maths is consistent everywhere. The
attribute system is redesigned around id-keyed storage with a handle-keyed field
collection in memory. `name`, `description` and `short_description` are now
dedicated translatable columns instead of living in `attribute_data`, and
`compare_price` is renamed to `list_price`.

## Admin & Filament v5

The admin panel is rebuilt on Filament v5 with the schemas refactor, and the
Filament integration is extracted into a `lunarphp/filament` bridge package that
plugs into any Filament v5 panel via `LunarPlugin::make()`. It ships reusable
entity-selector components, a first-party actions library (refund, capture,
fulfilment status, stock adjust, bulk publish, and more) and global-search
descriptors. Admin resources are publishable, and Staff moved into core so
non-Filament panels can share it.

## Foundations & external addressing

- **`\Lunar\Core` namespace** — the core package moved to `Lunar\Core\…`.
- **`public_id` (ULID)** — every standalone model now carries a stable,
  non-sequential external identifier (pivots and ISO-code models excluded),
  so integrations, webhooks and APIs have something safe to address records by.
- **Flat migration baseline** — v2 ships a single flat migration set; schema
  changes land as new migrations, never edits to the baseline.
- **Optional order-line purchasables** — `order_lines.purchasable_*` is nullable
  and shipping options are no longer stored as a fake polymorphic purchasable.

## Upgrading from v1

A `lunarphp/upgrade` package handles the v1 → v2 transition using Rector for
code-surface renames and one-way data migrations for schema changes. Data
migrations do not reverse — back up before running them.
