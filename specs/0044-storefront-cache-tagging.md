# 0044 — Storefront cache tagging and dependency resolution

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-30
- TODO item: Storefront caching toolkit (render-time dependency resolution)

## Problem

Spec [[0043-cache-invalidation-and-events]] gives a storefront a reliable signal that an entity changed: one event per entity, carrying a stable tag, and the storefront invalidates by tag. That is the *write* side. The *read* side is still unserved.

To cache a page under that model, a storefront must, at render time, attach the tags of **everything the page depends on** — a product page depends on `product:67`, its `brand:123`, its `collection:45`/`collection:46`, its `product-option:5`, and its cross-sold products. Today the storefront has to assemble that set by hand, which:

- duplicates Lunar's relationship knowledge in every consumer, and drifts when the schema changes;
- is easy to get wrong in the dangerous direction — a forgotten dependency means a page that never invalidates (the false negative 0043 exists to avoid);
- has no first-party home, so every storefront reinvents it.

Tag invalidation also only answers "this changed, drop it." For content that has *not* changed, a storefront wants cheap conditional responses (HTTP `304`) instead of re-rendering — and there is no first-party, dependency-aware version/ETag to drive that.

0043 deliberately left this to a follow-on: a model owns its **identity** (`cacheTags()`) and its **structural** cascade, but a page's **composition** — which entities it renders — is store-specific and belongs in a registry, not on the model.

## Proposal

A source-side toolkit in `Cache/` that a storefront uses at the moment it caches a page: resolve the dependency **tag set** to attach, and a dependency-aware **version stamp** for conditional requests. Both read the same declared graph that 0043's invalidation reads backwards.

### Part A — named dependency graphs + resolver

**A graph is a named, registerable definition of what a page composed from a root entity depends on.** Core ships defaults; a storefront registers or overrides its own. Because composition is store-specific, this is a registry, never a model method.

```php
use Lunar\Core\Facades\CacheDependencies;

// in a service provider boot()
CacheDependencies::define('product-display', [
    'brand',
    'collections',
    'productOptions',
    'associations.target',
]);

// a closure for cases a relation list can't express
CacheDependencies::define('product-display', fn (Product $product) => [
    ...$product->collections->modelKeys(),
]);
```

A definition is either a list of **relation paths** (dot-notation, walked from the root) or a **closure** returning models/tags. Core registers a default graph per cacheable morph type (`product` -> `product-display`, `collection` -> `collection`, `brand` -> `brand`, `product_option` -> `product-option`).

**The resolver** turns `(graph, root)` into a deduped tag set:

```php
use Lunar\Core\Facades\CacheTags;

$tags = CacheTags::for($product);                    // the product's default graph
$tags = CacheTags::for($product, 'product-display'); // a named graph
// => ['product:67', 'brand:123', 'collection:45', 'collection:46', 'product-option:5']
```

Algorithm: start with the root's own `cacheTags()`; for each relation path, `loadMissing()` the path (strict-lazy-load safe) and reduce segment by segment to the leaf models; union the `cacheTags()` of every reached **cache-participating** model (intermediate non-cacheable hops — e.g. `ProductAssociation` on the way to its `target` — are traversal only); dedupe. Satellites (variants, prices, stock) are **not** listed in a graph — their changes already fold into the parent's tag via 0043, so the parent tag covers them.

Listings (a collection page rendering N products) depend on the specific items shown, which the storefront already holds paginated — it unions the collection's graph tags with each item's own `cacheTags()` (the per-model accessor from 0043). The graph resolver is for the detail-page "depends on related entities" case; it is not asked to enumerate a paginated set.

`Cache\CacheDependencies` (bound to `Contracts\CacheDependencies`) holds the registry; `Cache\DependencyResolver` (bound to its contract) performs the walk; `Facades\CacheTags` is the ergonomic entry. Registration and the default-graph map live in `LunarServiceProvider`.

### Part B — dependency-aware version stamps

A page's ETag must change when **any** entity it depends on changes — including a satellite cascade (a variant price edit that never touches the product row). The events from 0043 already fire on exactly those changes, so versioning rides them rather than `updated_at` (which a satellite change does not bump).

**Generational versioning keyed by tag.** A version store maps each tag to a counter; a listener bumps it on every invalidation:

```php
use Lunar\Core\Facades\CacheVersion;

$etag = CacheVersion::for($product);  // e.g. "v:9f1c…" — stable until a dependency changes
```

- `Listeners\BumpCacheVersion` listens on `CacheInvalidationEvent` and increments the version of each of the event's `cacheTags()` in the store. (Unlike the reindex listener it fires on every reason — any invalidation means that tag's content changed.)
- `CacheVersion::for($model, $graph)` resolves the dependency tag set (reusing Part A), reads each tag's version, and combines them into a stable digest (`md5` of the sorted `tag:version` pairs). A tag never invalidated reads as version `0`.
- The store is a thin wrapper over Laravel's cache (`Cache\CacheVersionStore`), using a configurable store. Correctness needs a **shared, persistent** cache store in production (Redis/database, not `array`) — documented as a requirement; with no shared store the digest still works per-process but resets on flush (safe: a reset digest just forces one revalidation).

Combined, a storefront caches a page in two lines:

```php
$tags    = CacheTags::for($product);
$version = CacheVersion::for($product);

// tag-based store invalidation + an ETag for conditional GETs
Cache::tags($tags)->remember("product:{$product->id}:{$version}", $ttl, fn () => render($product));
```

### Homes

- `Cache\CacheDependencies`, `Cache\DependencyResolver`, `Cache\CacheVersionStore` — the machinery, alongside `CacheInvalidator`.
- `Contracts\CacheDependencies`, `Contracts\DependencyResolver`, `Contracts\CacheVersionStore` — the seams.
- `Facades\CacheTags`, `Facades\CacheVersion` — ergonomic entries (two single-purpose facades; see Open questions on merging them).
- `Listeners\BumpCacheVersion` — the version bump, registered next to `ReindexOnCacheInvalidation`.
- A `lunar.cache` config block for the version store name and the default-graph map.

## Alternatives considered

- **`cacheDependencies()` on the model.** Rejected by 0043's identity/composition split: page composition is store-specific, so it cannot live on a model shared by all stores.
- **Version from `max(updated_at)` over the dependency set.** Simpler (no store, no listener), but a satellite change (variant price) does not bump the parent's `updated_at`, so the stamp would miss exactly the cascade cases 0043 was built to catch. Rejected in favour of riding the invalidation events.
- **A per-entity `cache_version` DB column bumped on invalidation.** Durable and queryable, but adds a schema column and a write per invalidation on the hot path. Rejected for the cache-store approach (no migration, cheaper, and the store is already where tag invalidation lives).
- **Ship no defaults, registry only.** Forces every storefront to define `product-display` from scratch. Rejected — the defaults are the common case and double as documentation.
- **Do nothing.** Leaves every storefront hand-assembling tag sets and with no conditional-request story. Rejected — it is the read-side half of 0043.

## Migration impact

- **Database migrations:** none (the version store uses Laravel's cache).
- **Breaking changes:** none — purely additive. New public surface: `CacheDependencies` / `DependencyResolver` / `CacheVersionStore` (+ contracts), `Facades\CacheTags` / `Facades\CacheVersion`, the default graphs, `BumpCacheVersion`, and the `lunar.cache` config block. Changing any of it later needs a spec.
- **Upgrade path:** none required; consumers opt in.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none.

## Open questions

- **One facade or two?** `CacheTags::for()` + `CacheVersion::for()` (two single-purpose) vs. a single `PageCache::tags()/version()`. Leaning two — each reads cleanly at the call site — but a merged facade is one import. **Owner: slice 1.**
- **Graph definition validation.** Reject a relation path that does not exist on the root at registration time (fail fast), or resolve leniently and skip unknown paths? Leaning fail-fast in a non-production environment. **Owner: slice 1.**
- **Default-graph naming.** `product-display` vs `product` for the per-type default. The default-graph map keys on morph type, so the graph name is free; pick names that read well in consumer overrides. **Owner: slice 1.**
- **Version digest stability across deploys.** The digest combines tag versions from the store; a store flush resets to `0` and forces one revalidation (safe). Confirm no consumer needs a deploy-stable ETag (would need a deploy salt). **Owner: slice 2.**
- **Listing helper.** Is the documented "union the collection graph with each item's `cacheTags()`" recipe enough, or is a `CacheTags::forMany($models)` convenience worth adding? **Owner: slice 1.**

## References

- The invalidation/write side this completes: [[0043-cache-invalidation-and-events]] (see its Future direction).
- Identity vs composition split, and why graphs are a registry not a model method: 0043 Future direction.
- Registry/facade precedent: `Manifests/`, `OrderNotificationManifest`, `Facades\*`.
- Catalog relations the default graphs walk: `Product::{brand,collections,productOptions,associations}`, `Collection::{products,channels}`, `Brand::{products,collections}`, `ProductOption::{products,productOptionValues}`.
- Generational cache versioning (key-as-version) prior art: Rails `cache_versioning` / Russian-doll caching.

## Implementation plan

- [ ] Slice 1 — Dependency resolution. `Contracts\CacheDependencies` + `Cache\CacheDependencies` registry, `Contracts\DependencyResolver` + `Cache\DependencyResolver` (relation-path + closure definitions, dotted traversal, strict-lazy safe, dedupe), `Facades\CacheTags`, the default graphs + default-graph map, `lunar.cache` config. Tests: the product-display default resolves the expected tag set, a custom/overridden graph, a closure graph, a dotted path (`associations.target`), and that a non-cacheable hop is traversed not tagged.
- [ ] Slice 2 — Version stamps. `Contracts\CacheVersionStore` + `Cache\CacheVersionStore`, `Listeners\BumpCacheVersion` on `CacheInvalidationEvent`, `Facades\CacheVersion`. Tests: a digest is stable until a dependency is invalidated, a satellite cascade bumps it (variant change -> product digest changes), and an independent entity's change does not.
- [ ] Slice 3 — Contract + docs. Document the default graphs, the registration recipe, and the two-line page-caching recipe; port to the v2 docs when they land.
