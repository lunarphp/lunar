# 0044 — Storefront cache tagging and dependency resolution

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-30
- TODO item: Storefront caching toolkit (render-time dependency resolution)

## Problem

Spec [[0043-cache-invalidation-and-events]] gives a storefront a reliable signal that an entity changed: one event per entity, carrying a stable tag, and the storefront invalidates by tag. That is the *write* side. The *read* side is still unserved.

To cache a page under that model, a storefront must, at render time, attach the tags of **everything the page depends on** — a product page depends on `product:67`, its `brand:123`, its `collection:45`/`collection:46`, its `product-option:5`, and its cross-sold products. Today the storefront assembles that set by hand, which:

- duplicates Lunar's relationship knowledge in every consumer, and drifts when the schema changes;
- is easy to get wrong in the dangerous direction — a forgotten dependency means a page that never invalidates (the false negative 0043 exists to avoid);
- has no first-party home, so every storefront reinvents it.

0043 deliberately left this to a follow-on: a model owns its **identity** (`cacheTags()`) and its **structural** cascade, but a page's **composition** — which entities it renders — is store-specific and belongs in a registry, not on the model.

## Proposal

A source-side helper in `Cache/` that turns a page's root entity into the dependency **tag set** the storefront attaches when caching it — reading the same declared graph that 0043's invalidation reads backwards.

Scope is deliberately just tag resolution. Versioning / ETags are a fact about *how a consumer caches* (server-side tag store vs CDN conditional requests vs none), not about Lunar's data, so they stay with the consumer — see "Versioning is the consumer's concern" below.

### Named dependency graphs + resolver

**A graph is a named, registerable definition of what a page composed from a root entity depends on.** Core ships defaults; a storefront registers or overrides its own. Because composition is store-specific, this is a registry, never a model method.

```php
use Lunar\Core\Facades\CacheDependencies;

// in a service provider boot()
CacheDependencies::define('product', [
    'brand',
    'collections',
    'productOptions',
    'associations.target',
]);

// a closure for cases a relation list can't express
CacheDependencies::define('product', fn (Product $product) => [...]);
```

A definition is either a list of **relation paths** (dot-notation, walked from the root) or a **closure** returning models/tags. The default graph for an entity is named after its **morph alias** (`product`, `collection`, `brand`, `product_option`); core registers those, and a consumer overrides one by redefining it or adds view-specific graphs (`product-card`, `quick-view`) alongside.

**The resolver** turns `(graph, root)` into a deduped tag set:

```php
use Lunar\Core\Facades\CacheTags;

$tags = CacheTags::for($product);            // the graph named after the model's morph alias
$tags = CacheTags::for($product, 'product-card'); // a named graph
// => ['product:67', 'brand:123', 'collection:45', 'collection:46', 'product-option:5']
```

Algorithm: start with the root's own `cacheTags()`; for each relation path, `loadMissing()` the path (strict-lazy-load safe) and reduce segment by segment to the leaf models; union the `cacheTags()` of every reached **cache-participating** model (intermediate non-cacheable hops — e.g. `ProductAssociation` on the way to its `target` — are traversal only); dedupe. Satellites (variants, prices, stock) are **not** listed in a graph — their changes already fold into the parent's tag via 0043, so the parent tag covers them.

**Unknown relation paths fail loud where it's safe and never crash a live page** (mirroring `preventLazyLoading`): outside production a bad path throws, so a typo — or a relation removed by a later Lunar upgrade — is caught in dev/CI; in production the path is skipped and a warning logged, so the page renders and the silent drop is still surfaced.

`Cache\CacheDependencies` (bound to `Contracts\CacheDependencies`) holds the registry; `Cache\DependencyResolver` (bound to its contract) performs the walk; `Facades\CacheTags` is the ergonomic entry returning `array<string>`. Registration of the defaults lives in `LunarServiceProvider`; a `lunar.cache` config block carries nothing yet beyond room for future graph config.

### Listings

A listing page (a collection rendering N products) depends on the collection **and** the specific items shown, which the storefront already holds paginated. The item-level dependency is each item's own tag — the per-model accessor 0043 ships — so the recipe is a one-liner, no extra API:

```php
$tags = [
    ...CacheTags::for($collection),
    ...$products->flatMap->cacheTags(),
];
```

### Versioning is the consumer's concern

Tag invalidation (`Cache::tags()`) already gives "drop when changed" with no versions. A *dependency-aware ETag* (for HTTP conditional requests) is a further optimisation only some storefronts want, and it can't be both universal and correct without Lunar dictating caching infrastructure: an `updated_at`-derived stamp misses satellite cascades (a variant price change never touches the product row — the very case 0043 exists for), and a correct generational stamp needs a version store, and deploy-stability needs a salt — all of which are the consumer's caching-architecture decisions, not data decisions.

So Lunar ships the facts (the tag set here, the events in 0043) and documents the recipe rather than shipping the machinery. A storefront that wants ETags bumps a per-tag counter on invalidation, on its own store, and digests the page's dependency tags:

```php
// consumer code, on the consumer's store
Event::listen(CacheInvalidationEvent::class, function ($event) {
    foreach ($event->cacheTags() as $tag) {
        Cache::increment("cache-version:{$tag}");
    }
});

$etag = md5(collect(CacheTags::for($product))
    ->map(fn ($tag) => $tag.':'.(Cache::get("cache-version:{$tag}") ?? 0))
    ->sort()->implode('|'));
```

This rides the 0043 events, so it is correct by construction (it bumps on exactly the changes that invalidate), while leaving the store, persistence, and deploy-stability choices to the consumer.

### Homes

- `Cache\CacheDependencies`, `Cache\DependencyResolver` — the machinery, alongside `CacheInvalidator`.
- `Contracts\CacheDependencies`, `Contracts\DependencyResolver` — the seams.
- `Facades\CacheTags` — the ergonomic entry.
- Registration of the default graphs in `LunarServiceProvider`.

## Alternatives considered

- **`cacheDependencies()` on the model.** Rejected by 0043's identity/composition split: page composition is store-specific, so it cannot live on a model shared by all stores.
- **Ship version stamps / ETags in core.** Considered and rejected: no version mechanism is both universal and correct. `updated_at` misses satellite cascades; a generational stamp needs an opinionated store plus salt/persistence choices that are the consumer's caching-architecture decisions, not Lunar's. Tags and events are facts about Lunar's data; versioning is a fact about how the consumer caches — so it is left to the consumer as a documented recipe built on the shipped tags + 0043 events.
- **Ship no defaults, registry only.** Forces every storefront to define `product` from scratch. Rejected — the defaults are the common case and double as documentation.
- **A `CacheTags::forMany()` listing helper.** Rejected — the listing recipe (union the collection graph with each item's `cacheTags()`) is a trivial one-liner over existing surface, and `forMany` would carry an ambiguous meaning (each item's own tag vs each item's full graph).
- **Do nothing.** Leaves every storefront hand-assembling tag sets. Rejected — it is the read-side half of 0043.

## Migration impact

- **Database migrations:** none.
- **Breaking changes:** none — purely additive. New public surface: `CacheDependencies` / `DependencyResolver` (+ contracts), `Facades\CacheTags`, the default graphs, and a `lunar.cache` config block. Changing any of it later needs a spec.
- **Upgrade path:** none required; consumers opt in.
- **Translation / locale impact:** none (bar any new exception messages, which still need all 16 locales if added).
- **Filament / admin impact:** none.

## Open questions

- ~~Facade shape.~~ **Resolved:** `CacheTags::for()` returns the tag array directly — with version stamps dropped there is nothing to bundle, so no descriptor.
- ~~Graph validation.~~ **Resolved:** throw on an unknown relation path outside production (caught in dev/CI); skip + log a warning in production (never crash a live page). Mirrors `preventLazyLoading`.
- ~~Default-graph names.~~ **Resolved:** entity-named (`product`, `collection`, `brand`, `product_option`), matching the tag morph vocabulary; consumers add view-specific graphs (`product-card`) on top.
- ~~Version stamps.~~ **Resolved:** dropped — versioning/ETags are the consumer's caching concern, documented as a recipe on the shipped tags + 0043 events, not shipped machinery.
- ~~Listing helper.~~ **Resolved:** no helper; the listing recipe is documented.

## References

- The invalidation/write side this completes: [[0043-cache-invalidation-and-events]] (see its Future direction).
- Identity vs composition split, and why graphs are a registry not a model method: 0043 Future direction.
- Registry/facade precedent: `Manifests/`, `OrderNotificationManifest`, `Facades\*`.
- Catalog relations the default graphs walk: `Product::{brand,collections,productOptions,associations}`, `Collection::{products,channels}`, `Brand::{products,collections}`, `ProductOption::{products,productOptionValues}`.

## Implementation plan

- [ ] Slice 1 — Dependency resolution. `Contracts\CacheDependencies` + `Cache\CacheDependencies` registry, `Contracts\DependencyResolver` + `Cache\DependencyResolver` (relation-path + closure definitions, dotted traversal, strict-lazy safe, dedupe, strict-dev/lenient-prod path validation), `Facades\CacheTags`, the entity-named default graphs, `lunar.cache` config. Tests: the `product` default resolves the expected tag set, an overridden default, a custom named graph, a closure graph, a dotted path (`associations.target`), a non-cacheable hop is traversed not tagged, and an unknown path throws outside production / is skipped + logged in production.
- [ ] Slice 2 — Contract + docs. Document the default graphs, the registration recipe, the page-caching recipe (incl. listings), and the optional consumer versioning recipe; port to the v2 docs when they land.
