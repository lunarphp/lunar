# 0043 — Cache invalidation and event coverage

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: Add events, including specific events for cache invalidation

## Problem

Lunar is headless. A storefront renders and caches pages (a product display page, a collection listing, a brand page) and needs a reliable signal when the underlying data changes so it can expire the right caches. Today there is no such signal, and the events that exist for catalog changes are in the wrong layer, fire from the wrong place, and feed only one consumer.

**Catalog change events live in the admin layer and only fire from the UI.** The events that detect catalog changes — `ProductAssociationsUpdated`, `ProductCollectionsUpdated`, `ModelPricesUpdated`, `ModelMediaUpdated`, `ModelUrlsUpdated`, `ModelChannelsUpdated`, `CollectionProductAttached`/`Detached`, `ProductVariantOptionsUpdated`, `ProductCustomerGroupsUpdated` — are defined in `packages/filament` (mirrored in `packages/admin`) and dispatched **only** from Filament relation managers and `BaseEditRecord::afterSave()`. A change made through an action, a console command, a bulk operation, or a future storefront API fires nothing. Their sole consumer is one inline closure per event in `LunarPanelProvider` calling `sync_with_search($event->model)`.

**Core catalog models are nearly event-silent.** `Product` fires only `ProductStatusUpdated`. `ProductVariant`, `Price`, `Collection`, `Brand`, `ProductOption`, `Url` fire nothing of their own on create/update/delete. `ProductAssociation` is mutated through the `Associate` / `Dissociate` jobs with no event at all — so the scenario that motivates this spec (an admin edits a product and adds an associated product) produces no core signal that either product changed.

**There is no cache-invalidation contract.** A storefront has nothing first-party to listen to, no statement of which change invalidates which entity, and no guarantee a signal fires for programmatic changes. Reindexing has exactly the same shape (a change to data should refresh a derived view) and rides the same broken plumbing — so the search index also silently misses programmatic changes.

The hard part is reliability under a real performance constraint. A brand rename appears on every product page of that brand; a naive design fans out to thousands of product invalidations per edit. We cannot afford that cost, and we equally cannot afford a cache that fails to invalidate when it should.

## Proposal

Two parts, separated so the reliability mechanism is not muddied by a long enumeration:

- **Part A — a cache-invalidation contract in core.** A small set of semantic invalidation events for the independently-cacheable catalog entities, emitted reliably (after commit, deduped, complete) and driven off model lifecycle and relation verbs rather than the UI. Bounded structural cascades only; no fan-out.
- **Part B — domain lifecycle event coverage.** Fill the standalone (non-cascading) event gaps across customer, cart, and discount for general extensibility, on the existing event convention.

### The invalidation model: one event per entity, tag-based downstream

Lunar emits **one event per changed entity** and never enumerates dependents. A brand rename fires a single `BrandInvalidated` carrying the tag `brand:{id}`. The storefront caches each page with the tags of the entities it depends on (`["product:67", "brand:123", "collection:45"]`) and invalidates by tag — so dropping `brand:123` expires every dependent page in one operation, with zero fan-out on Lunar's side. The match is **complete by construction**: a page tagged `brand:123` cannot be missed. This is O(1) to emit and needs no queue. It assumes a tag-capable cache (Redis cache tags, Fastly/Cloudflare/Varnish surrogate keys) — which is the storefront's choice, not Lunar's, and Lunar documents it as the expected pattern.

Lunar's obligation is therefore **completeness and reliability of the event stream**, not dependent-page enumeration:

- **Complete** — every mutation of a cacheable entity emits, regardless of entry point (action, console, bulk, API, UI). This is why detection moves to core model lifecycle, not the admin UI.
- **Deduped** — one admin save that touches a product, its prices, and its channels emits `ProductInvalidated` once, not a storm.
- **After commit** — events flush only when the surrounding database transaction commits, so a rolled-back change never invalidates and a committed one always does.
- **Stable keys** — each event exposes a durable tag derived from a single `cacheKey()` seam, so the tag scheme can move from primary key to `public_id` (see TODO "Add `public_id`") in one place when that lands.

The guiding asymmetry: a **false positive** (invalidating something that did not really change) costs one cache miss and a recompute; a **false negative** (a missed invalidation) serves stale data indefinitely. Wherever there is doubt, the mechanism biases toward firing. Each event also **captures its tags and morph-key as scalars at record time**, keeping the model only as a convenience — so a delete invalidation, and any queued listener (e.g. a future webhook), works without rehydrating a row that no longer exists.

### Bounded structural cascades (in core) vs. unbounded references (downstream)

Core cascades only the **bounded structural** relationships — those where the relationship itself is the thing that changed:

| Change | Invalidates |
| --- | --- |
| `Product` saved / deleted / restored | that Product |
| `ProductVariant` saved / deleted | parent Product |
| `Price` saved / deleted | the priceable's Product |
| `StockLevel` change (via `adjustStock`) | the variant's Product (availability on the page) |
| `ProductAssociation` created / deleted | **both** parent Product and target Product |
| product <-> collection membership attach / detach | that Product **and** that Collection |
| product <-> customer-group pivot change | that Product |
| channelable pivot change (`HasChannels`) | the owning entity |
| media attach / detach (`HasMedia`) | the owning entity |
| `Url` saved / deleted | the owning element (Product / Collection) |
| `ProductOptionValue` saved / deleted | its ProductOption |
| `Collection` saved / deleted | that Collection |
| `Collection` re-parented (nested-set move) | the moved Collection **and its descendant subtree** |
| `Brand` saved / deleted | that Brand |
| `ProductOption` saved / deleted | that ProductOption |

The **unbounded** "this entity is referenced by many pages" relationships (brand -> its products, collection -> its members, shared option -> its products) are **never** fanned out. They emit the single entity event (`BrandInvalidated`, `CollectionInvalidated`, `ProductOptionInvalidated`); the storefront tags its product pages with `brand:123` / `collection:45` / `product-option:5` and invalidates by tag. A page that shows variant/price/stock for product 67 already depends on `product:67`, and the bounded cascade above guarantees a direct edit to any of those emits `ProductInvalidated(67)`.

The collection-move cascade is bounded **structural**, not a fan-out: a re-parent changes a structural fact (ancestry, hence rendered path/breadcrumb) about each descendant, so it emits one `CollectionInvalidated` per affected node — bounded by subtree size (tens, not the product count). Products in those collections are covered downstream by tag (a page tagged `collection:45` invalidates when `collection:45` fires), so no product fan-out is needed.

`ProductOption` is independently cacheable for the same no-fan-out reason: a shared option ("Size") is used by many products, so the only alternative to a per-option event is fanning out option -> products on a rename — exactly what is ruled out. Making the option its own taggable entity (`product-option:{id}`) is what lets a storefront that renders the variant selector tag its pages on the option and invalidate without fan-out. `ProductOptionValue` stays a satellite routing to its option (a value rename -> the option tag -> tagged pages).

### Part A — shape

**Independently-cacheable entities** (each gets a typed event and a tag): `Product`, `Collection`, `Brand`, `ProductOption`. **Satellites** (no event of their own; they invalidate a cacheable target): `ProductVariant`, `Price`, `Url`, `ProductAssociation`, `ProductOptionValue`, media/channel/customer-group pivots.

**Events** — `Lunar\Core\Events\Catalog\{ProductInvalidated, CollectionInvalidated, BrandInvalidated, ProductOptionInvalidated}`, each implementing the marker contract so a consumer can listen narrowly (one type) or broadly (the interface — Laravel dispatches to listeners registered against an event's implemented interfaces):

```php
namespace Lunar\Core\Events\Catalog;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Product;

class ProductInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Product $product,
        public CacheInvalidationReason $reason,
    ) {}

    /** @return array<string> */
    public function cacheTags(): array
    {
        return $this->product->cacheTags();
    }
}
```

`Lunar\Core\Contracts\CacheInvalidationEvent` declares `cacheTags(): array` and exposes the model + `CacheInvalidationReason` (an `Enums\CacheInvalidationReason` of `Created`, `Updated`, `Deleted`, `RelatedChanged`). `reason` is informational for listeners and future webhook payloads; it does not change which tag invalidates.

**`InvalidatesCache` concern** (`Models\Concerns\InvalidatesCache`) — for the four cacheable entities. Provides `cacheTags()` (default `["{$this->getMorphClass()}:{$this->cacheKey()}"]`), `cacheKey()` (default primary key), `cacheInvalidationTargets(): iterable` (default `[$this]`), a manual `invalidateCache()` model verb, and boots `saved` / `deleted` to record itself with the invalidator.

**`InvalidatesRelatedCache` concern** (`Models\Concerns\InvalidatesRelatedCache`) — for satellites. Declares `cacheInvalidationTargets(): iterable` returning the cacheable entities affected, and boots lifecycle to record those targets. Examples:

```php
// ProductVariant
public function cacheInvalidationTargets(): iterable
{
    return [$this->product];
}

// ProductAssociation
public function cacheInvalidationTargets(): iterable
{
    return [$this->parent, $this->target];
}

// Price
public function cacheInvalidationTargets(): iterable
{
    $priceable = $this->priceable;

    return [$priceable instanceof ProductVariant ? $priceable->product : $priceable];
}
```

**`CacheInvalidator`** — a request-scoped service (bound to `Contracts\CacheInvalidator`) that collects records, dedupes by tag (strongest reason wins: `Deleted` > `Updated` > `Created`/`RelatedChanged`), and flushes one event per cacheable entity when the surrounding transaction commits (registered on the connection's after-commit callback when inside a transaction; on `app()->terminating()` otherwise). `record(Model $target, CacheInvalidationReason $reason)` is the single entry point; the concerns call it on lifecycle, and relation-mutation seams (below) call it directly.

**Relation-mutation tier.** Eloquent fires no model event for pivot attach/detach (a raw pivot insert), which is exactly why the admin-layer events were invented. Rather than introduce Lunar-specific verbs a developer would have to discover, the **native Eloquent relationship API is the seam**: `Models\Base` returns a `Relations\BelongsToMany` / `Relations\MorphToMany` (via `newBelongsToMany()` / `newMorphToMany()`) whose `attach`/`detach`/`sync`/`toggle`/`updateExistingPivot` record invalidation for the relation's parent and any cache-participating related models. So `$product->collections()->attach($id)` invalidates both the product and the collection with nothing custom to learn; recording is a no-op for models that do not participate in cache invalidation. Bulk operations that bypass model events are likewise routed through per-model deletes — the `Dissociate` job deletes associations one by one so the `deleted` event (and its cascade) fires.

### Search reindexing rides this

The 12 inline `sync_with_search` closures in `LunarPanelProvider` and the `BaseEditRecord::afterSave()` call collapse to listeners on the four core invalidation events: `ProductInvalidated` -> reindex Product, `BrandInvalidated` -> reindex Brand, `ProductOptionInvalidated` -> reindex ProductOption (`CollectionInvalidated` reindexes only if Collection becomes searchable). Reindexing then works for programmatic changes too, closing the existing gap. The admin-layer `*Updated` events are removed.

### The storefront contract

Part A publishes a table — entity, its event, its default tag, and the changes that trigger it (including the bounded cascades above) — as the authoritative statement of "what to listen to and how to tag." It lands in the spec and ports to the v2 docs when they land.

### Part B — domain lifecycle event coverage

Standalone events (no cascade), on the existing convention (plain class; `Dispatchable, SerializesModels`; promoted props carrying the model and, where relevant, before/after state; dispatched from the model verb / action / observer). Initial set:

- **Customer** — `CustomerCreated`, `CustomerUpdated`, `CustomerDeleted` (the admin-only `CustomerUserEdited` / `CustomerAddressEdited` retire in favour of these).
- **Cart** — `CartCreated`, `CartLinesUpdated`, `CartMerged`, `CartDeleted`.
- **Discount** — `DiscountCreated`, `DiscountUpdated`, `DiscountDeleted`.

Orders, fulfilment, and payment are already well covered and are untouched. The convention makes further additions mechanical; this set is the proposed initial coverage, not a ceiling.

Two boundaries are explicit:

- **`CartAbandoned` is out of scope.** Abandonment is not a lifecycle event — it needs a time-based sweep and a heuristic (like the stock-release sweep), so it is its own small feature, not a Part B event.
- **`Discount*` events are extensibility hooks, not cache-invalidation wiring.** A discount applies conditionally to an unbounded product set, so plumbing it into Part A would reopen the fan-out problem under a different name. Storefronts typically cache discounted prices on a coarse TTL; promotion -> price-display invalidation is deliberately not attempted here.

## Alternatives considered

- **Source-side fan-out.** On a brand/collection/option change, enumerate every dependent product and emit a per-product invalidation through queued jobs. Works for caches without tag support, but emits thousands of events per edit and depends on reliable queue infrastructure — the performance hit this spec exists to avoid. Rejected as the model; a consumer whose cache lacks tags can still build a dependency index from the complete entity stream.
- **Lean on raw Eloquent model events.** Consumers already can `Product::observe(...)`. Rejected: Eloquent fires nothing for pivot attach/detach or the association jobs (the motivating case), fires noisily on internal denormalisation writes, carries no semantic "this cacheable entity is stale" meaning, no dedup, no after-commit guarantee, and no cascade — every consumer would re-derive the propagation graph.
- **A single generic `CatalogInvalidated` event** carrying the model and a type discriminator. Fewer classes, but every consumer switches on type anyway, and per-type events give a clean "what to listen to" contract and a clean future webhook-topic mapping. Rejected in favour of typed events plus the marker interface for broad listening.
- **Do nothing.** Leaves the storefront with no reliable signal and reindexing silently broken for programmatic changes. Rejected.

## Migration impact

- **Database migrations:** none. (Durable webhook/tag keys prefer `public_id`; until that lands, tags use morph type + primary key via the `cacheKey()` seam. Soft dependency, not a blocker.)
- **Breaking changes to the public contract surface:** the admin/filament `*Updated` catalog events are removed; their only first-party consumer (search reindexing) moves to the core events. Downstream code listening to them retargets to the core `*Invalidated` events. New public surface: the four events, the `CacheInvalidationEvent` contract, `CacheInvalidationReason`, the `InvalidatesCache` / `InvalidatesRelatedCache` concerns, `Contracts\CacheInvalidator`, and `$model->invalidateCache()`.
- **Upgrade path for v1.x consumers:** upgrade-package notes mapping removed admin events to the core events; a Rector rename where the mapping is 1:1 (e.g. `ProductCollectionsUpdated` -> listen to `CollectionInvalidated` / `ProductInvalidated`). Where semantics widen (core fires on more paths), document the behaviour change rather than a blind rename.
- **Translation / locale impact:** none (no user-facing strings beyond any new exception messages, which still need all 16 locales if added).
- **Filament / admin impact:** relation managers and `afterSave` stop dispatching their own events and call the core verb/recorder; the `sync_with_search` closures collapse to listeners on the core events.

## Open questions

- ~~New `Cache/` folder.~~ **Resolved (maintainer): approved.** A top-level `Cache/` under `packages/core/src/` houses the recorder and the wider source-side cache toolkit (see Future direction). Events stay under `Events/Catalog/`, the contracts in `Contracts/`, the enum in `Enums/`, the concerns in `Models/Concerns/` — only the machinery with no existing home lands in `Cache/`.
- ~~After-commit mechanism.~~ **Resolved: recorder-managed buffer, flushed by a single after-commit callback.** `ShouldDispatchAfterCommit` cannot dedup, so the recorder is needed regardless; a full rollback discards the callback, no-transaction falls back to a `terminating` flush. Savepoint/nested rollback may over-invalidate — accepted, since a false positive is a cheap recompute and a false negative serves stale data (the mechanism biases toward firing).
- ~~Collection nested-set moves.~~ **Resolved: invalidate the moved collection and its descendant subtree.** A re-parent is a bounded *structural* change to each descendant's ancestry (one `CollectionInvalidated` per node, bounded by subtree size); products are covered downstream by collection tag, so no product fan-out.
- ~~`ProductOption` as a cacheable entity.~~ **Resolved: yes.** The alternative (route option changes to their products) is the unbounded fan-out already ruled out, so the option must be its own taggable entity (`product-option:{id}`); `ProductOptionValue` stays a satellite routing to its option.
- ~~Part B breadth.~~ **Resolved: customer / cart / discount as the initial cut**, with `CartAbandoned` out of scope (sweep-based feature) and `Discount*` events as extensibility hooks not wired into cache invalidation. See Part B.

## Future direction

`Cache/` is the home for the source-of-truth-side cache toolkit — the primitives that make a storefront's cache layer cheap and reliable to build. It is not the storefront's cache store, HTTP middleware, or CDN; those stay with the consumer (or a later first-party storefront kit). The recorder is the first inhabitant. The anticipated additions below are non-binding and out of scope for this spec, recorded so the folder's intent is clear and it does not read as a one-class folder.

- **Render-time dependency resolution (the priority).** Invalidation reads the structural graph *backwards* — a variant change emits `product:67`. The same need read *forwards* is "what tags should a cached page carry?", and that answer is **store-specific**: one storefront's product display page renders brand, collections, and cross-sells; another renders none of them. So this is deliberately **not** a model method. It is a registry of **named dependency graphs**: core ships sensible defaults (`product-display`, `collection-listing`, `brand-page`) that walk declared relationships from a root entity and compose the tag set; a storefront registers its own graphs the same way, reusing the same relationship-walking primitives. A resolver turns `(graph, root)` into the tag set the storefront attaches when it caches the page.

  This keeps two concerns cleanly apart, and is why Part A's invalidation side stays on the model: a model owns its **identity** tag (`product:67`) and its **structural** cascade (variant -> product) — both store-agnostic facts about the data. A page's **composition** — which entities it actually renders, and therefore depends on — belongs to the store, in a registered graph. Identity and structure on the model; composition in the registry.

- **Change feed / sync cursor.** A persisted invalidation log a storefront can replay (`changes since X`) to reconcile or warm a cold cache after downtime, without a full rebuild — the catch-up path for changes an in-process listener can miss.
- **Version stamps.** A per-entity monotonic stamp the storefront can map to ETag / Last-Modified for conditional requests.
- **Lunar's own derived caches.** Internal computed caches (price breakdowns, availability rollups) keyed by the same tag vocabulary, so an invalidation busts them too.

Webhooks ride the same invalidation events but get their own home (`Webhooks/`); outbound delivery, retries, and signing are orthogonal to caching and do not belong under `Cache/`.

## References

- Existing event convention: `packages/core/src/Events/Orders/OrderPlaced.php`, `packages/core/src/Events/Fulfilment/FulfilmentStatusUpdated.php`.
- Admin-layer catalog events being retired: `packages/filament/src/Events/*`, dispatched from `packages/admin/src/Filament/.../RelationManagers` and consumed in `packages/admin/src/LunarPanelProvider.php` via `sync_with_search` (`packages/admin/src/helpers.php`).
- Search reindexing: `packages/core/src/Models/Concerns/Searchable.php`, `packages/core/src/Search/ScoutIndexer.php`.
- Product associations: `packages/core/src/Jobs/Products/Associations/{Associate,Dissociate}.php`, `packages/core/src/Models/ProductAssociation.php`.
- Inventory rollups and the `adjustStock` seam this rides for stock changes: [[0038-inventory-fundamentals]].
- Stable external keys for webhook/tag payloads: TODO "Add `public_id` (ULID) to externally-addressable models".

## Implementation plan

- [x] Slice 1 — Mechanism. `CacheInvalidationEvent` contract, `CacheInvalidationReason` enum, the four `Events\Catalog\*Invalidated` events, `InvalidatesCache` (cacheable entity) + `InvalidatesRelatedCache` (satellite) concerns, `Contracts\CacheInvalidator` + `Cache\CacheInvalidator` (dedup, strongest-reason-wins, after-commit flush via the connection's `afterCommit`, immediate when no transaction), `$model->invalidateCache()`. (`Cache/` already existed alongside `AttributeCache`.)
- [x] Slice 2 — Adopt on catalog. `Product`/`Collection`/`Brand`/`ProductOption` use `InvalidatesCache`; `ProductVariant`/`Price`/`Url`/`ProductAssociation`/`ProductOptionValue`/`StockLevel` declare `cacheInvalidationTargets()` (via `loadMissing`, strict-lazy-load safe). `tests/core/Feature/CacheInvalidationTest.php` covers create/update/delete reasons, each bounded cascade (associated-product add -> both products, variant/price save -> product, option value -> option, stock satellite -> product), dedup within a transaction, no emit on rollback, held-until-commit, and tag format. Uses `DatabaseMigrations` so the after-commit flush is exercised against real transaction semantics.
- [x] Slice 3a — Core bulk/structural gaps that bypass model events. `Dissociate` now deletes associations per-model (was a bulk `delete()`), so removing an association invalidates both products. `Collection` re-parent invalidates its descendant subtree (detected via kalnoy `hasMoved()`, descendants re-queried from a fresh copy since the moved node's bounds sync after the `saved` event). (Adding an association was already covered — `Associate` uses `createMany`, which fires model events.)
- [x] Slice 3b — Native pivot hooking. `Models\Base` returns `Relations\BelongsToMany` / `Relations\MorphToMany` (shared `RecordsCacheInvalidation` trait) so native `attach`/`detach`/`sync`/`toggle`/`updateExistingPivot` record invalidation for the parent and cache-participating related models — `$product->collections()->attach($id)` invalidates both sides, no Lunar-specific verb to learn. Recording is a guarded no-op for non-participating models (Cart/Order/Staff/etc.). Tests cover attach/sync/detach (both directions) and a non-cacheable related side (`channels()`). Full suite (core/admin/search/shipping, 1218) green. (Media is a spatie morphMany, not a pivot — a separate path, deferred.)
- [ ] Slice 4 — Retire admin `*Updated` events + reindex on core events. Remove the 14 `packages/filament` catalog events and the `sync_with_search` listener block; retarget reindexing onto the core invalidation events (so programmatic changes reindex too). Breaking (admin-layer surface); upgrade note maps the removed events to the core ones.
- [ ] Slice 5 — Part B lifecycle events. Customer, cart, and discount events dispatched from their verbs/observers; retire the admin-only customer events.
- [ ] Slice 6 — Upgrade + contract. Upgrade-package Rector/notes for the removed admin events; publish the storefront cache-invalidation contract table (spec now, docs when v2 docs land).
