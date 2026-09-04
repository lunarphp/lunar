# 0077 — API platform: `lunarphp/api` (storefront surface, admin surface, webhooks)

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-02
- TODO item: API platform — storefront and admin HTTP APIs plus outbound webhooks

## Problem

Lunar v2 is headless at the data layer and not at the wire. Every consumer that is not the Filament admin or the Inertia panel reaches Lunar through code they write themselves.

- **Headless storefronts** (Next.js, Nuxt, native apps) have no first-party endpoints. Each host app writes its own controllers around `Product`, `Cart`, `CartSession` and `StorefrontSession`, re-deriving pagination, filtering, price projection and cart write semantics. Two Lunar stores ship two incompatible product payloads.
- **Integrations** (ERP, PIM, OMS, marketplaces, sync jobs) have no way to read or write the catalogue, orders or customers from outside the monolith. The only administrative surfaces are the two admin UIs, both session-authenticated and HTML-shaped.
- **Add-ons have no wire extension seam.** A reviews add-on can ship models, migrations and panel sections, but has no agreed way to make `GET /products/{id}` return `average_rating`, accept `?include=reviews` or `?filter[min_rating]=4`.
- **Nothing leaves the process.** Spec 0043 built a complete, deduped, after-commit event stream for cache invalidation and domain lifecycle, and deliberately deferred the outbound half ("cache toolkit follow-ons: outbound webhooks, change feed"). A storefront on a different host still cannot learn that `brand:123` changed.
- **The existing draft** (`specs/draft-storefront-api.md`) designs a storefront REST API in isolation. It predates `public_id` (0046), the panel's extension conventions (0049) and the decision to also ship an admin surface, and it leaves auth for machine clients and outbound events unaddressed.

## Proposal

One new sub-package, `lunarphp/api` (`packages/api`, namespace `Lunar\Api`), depending on `lunarphp/core` only. It ships **one kernel** (resource model, query grammar, envelope, errors, versioning, extension registry) and **two surfaces** built on it: a **storefront** surface for customers and guests, and an **admin** surface for staff and machine integrations. **Webhooks** are the kernel's third consumer: the same resource serialisation that answers `GET /admin/v1/products/{id}` produces the `product.updated` payload.

This spec fixes the shared decisions. Three child specs carry the per-surface detail and their own implementation plans:

| Spec | Scope | Basis |
| --- | --- | --- |
| [[0078-storefront-api]] | Storefront v1 endpoints: catalogue, cart, checkout, customer area | `specs/draft-storefront-api.md`, revised per the deltas listed below |
| [[0079-admin-api]] | Admin v1 endpoints: catalogue, sales, inventory, settings; API keys | this spec |
| [[0080-webhooks]] | Topics, endpoints, deliveries, signing, retry | this spec, section E |

### A. Package layout

```
packages/api/
├── composer.json                     lunarphp/api, requires lunarphp/core
├── config/api.php                    merged as lunar.api.*
├── database/migrations/              api_keys, webhook_endpoints, webhook_deliveries
├── resources/lang/{16 locales}/      error titles and details
├── routes/storefront/v1.php
├── routes/admin/v1.php
└── src/
    ├── ApiServiceProvider.php
    ├── ApiManager.php                the registry behind Facades\Api
    ├── Facades/Api.php
    ├── Contracts/                    ApiManager, CustomerResolver, CartTokenCodec, WebhookDispatcher, ...
    ├── Resources/                    Resource, ResourceExtension, Field, Embed, Filter, Sort, SerializationContext
    ├── Query/                        QueryParser, IncludeLoader, FilterApplier, SortApplier, Paginator
    ├── Http/
    │   ├── Controllers/Concerns/     shared index/show/store/update/destroy plumbing
    │   ├── Middleware/               EnforceJson, ResolveApiVersion, ...
    │   ├── Responses/                Envelope, ErrorEnvelope
    │   └── Exceptions/               ApiException and subclasses, Handler
    ├── Storefront/
    │   ├── Http/Controllers/V1/, Http/Requests/V1/, Http/Middleware/
    │   └── Resources/V1/
    ├── Admin/
    │   ├── Http/Controllers/V1/, Http/Requests/V1/, Http/Middleware/
    │   ├── Resources/V1/
    │   └── Auth/                     ApiKeyGuard, ApiKeyUserProvider, Abilities
    ├── Models/                       ApiKey, WebhookEndpoint, WebhookDelivery
    ├── Webhooks/                     Topic, TopicRegistry, Dispatcher, Jobs/DeliverWebhook, Signer
    └── Console/                      lunar:api:key, lunar:api:webhook, lunar:api:schema
```

The surfaces share the kernel but never import from each other. If admin later needs to become its own Composer package, the split runs along the `Storefront/` / `Admin/` / `Webhooks/` namespace lines with the kernel staying in `lunarphp/api`; nothing in this design blocks that, and nothing forces it before the registry has been exercised by real add-ons.

No Filament or panel dependency. `lunarphp/search` optionally registers onto the storefront products resource (section F); the API package does not depend on it.

### B. Routing and versioning

Two prefixes, one per surface, each independently versioned in the path:

```
/api/storefront/v1/...      config lunar.api.storefront.prefix  (default api/storefront)
/api/admin/v1/...           config lunar.api.admin.prefix       (default api/admin)
```

- Each surface owns its own middleware group (`lunar.api.storefront`, `lunar.api.admin`), registered by the package like the panel registers `lunar.panel`, so a host appending middleware to its own `api` group cannot double-stack or break the package. Both groups run stateless: no `StartSession`, no CSRF, `throttle` and `SubstituteBindings` only, plus the surface's auth and context middleware.
- Routes register automatically; `lunar.api.{surface}.register_routes = false` lets a host require the route files itself. Either surface can be disabled outright (`enabled => false`).
- The path segment versions the **wire contract**, not the Lunar release. Both surfaces start at `v1` even though they ship in Lunar 2.x, and the counter only moves when a surface breaks its contract. Monorepo releases give every sub-package the same version number, so the package version could never be the source; and a future Lunar 3.0 that breaks PHP contracts but not the wire keeps serving `v1`. The Lunar v1.x API shares nothing with this contract, and the upgrade notes say so.
- A breaking change to a surface cuts a new version directory (`Storefront/Http/Controllers/V2`, `Storefront/Resources/V2`, `routes/storefront/v2.php`). Extensions register per surface and version: `Api::storefront('v1')->extend(...)`. Old versions stay loadable until removed by a spec.
- **Every model is addressed by `public_id`** (0046). Route binding resolves `{product}` through `wherePublicId()`; the integer key never appears in a URL or a payload. Models the 0046 amendment excludes from `public_id` because they carry an immutable standard code (`Currency`, `Language`, `Country`, `State`) are addressed by that code. Nested rows without their own endpoint (order lines, addresses, transactions) appear embedded under their parent with their own `public_id` as `id`.

### C. The kernel: resources, grammar, envelope

#### Resources serialise without a request

`Lunar\Api\Resources\Resource` is the unit of the API. It is not Laravel's `JsonResource`: that class is bound to the current `Request`, and webhook payloads (section E) and console output must serialise a model with no request in flight.

```php
abstract class Resource
{
    abstract public static function type(): string;          // 'products'
    abstract public static function model(): string;         // Product::class

    /** @return array<Field> */    public function fields(): array;
    /** @return array<Embed> */    public function includes(): array;
    /** @return array<Filter> */   public function filters(): array;
    /** @return array<Sort> */     public function sorts(): array;

    public function defaultPageSize(): int { return 15; }
    public function maxPageSize(): int { return 100; }

    /** Final. Applies sparse fieldsets and requested includes from the context. */
    public function toArray(Model $model, SerializationContext $context): array;
}
```

`SerializationContext` is a value object carrying: surface and version; requested fields per type; requested include tree; the `StorefrontContext` (0040) for storefront responses, `null` for admin; the locale; the authenticated principal. Controllers build it from the request; the webhook dispatcher builds it from config.

Fields, includes, filters and sorts are small value classes built with `make()` and a closure, so both the package and add-ons declare them the same way. The include value class is `Embed` (`include` is a reserved word in PHP and cannot name a class); the query parameter, the `includes()` method and the vocabulary stay "include":

```php
Field::make('name', fn (Product $product, SerializationContext $ctx) => $product->translate('name', $ctx->locale)),
Field::make('average_rating', fn (Product $product) => $product->reviews_avg_rating ?? 0)->eagerLoad(withAvg: ['reviews', 'rating']),
Field::make('cost_price', ...)->requires('catalog:manage-products'),   // dropped when the principal lacks the ability

Embed::relation('brand', BrandResource::class),                        // $model->brand()
Embed::make('reviews', fn (Product $p, SerializationContext $ctx) => $p->reviews, ReviewResource::class)->eagerLoad('reviews'),

Filter::exact('sku', column: 'sku'),
Filter::make('price', fn (Builder $q, mixed $value, string $operator, SerializationContext $ctx) => ...)->operators(['eq', 'gte', 'lte']),
Filter::scope('featured'),        // any native or Builder::registerScope() scope (0042)

Sort::column('name'),
Sort::make('rating', fn (Builder $q, string $direction) => $q->orderBy('reviews_avg_rating', $direction)),
```

Money serialises from `PriceValue` as `{ "amount": 1999, "currency": "GBP", "decimal_places": 2, "formatted": "£19.99" }`. Translatable fields serialise as the resolved string on the storefront surface and as the full locale map on the admin surface, matching how the admin writes them. Dates are ISO 8601 UTC. Enum-backed states serialise as their string value.

#### Registry and extension

`Lunar\Api\ApiManager` (behind `Facades\Api`) holds resources per surface and version. Registration mirrors the panel's `Panel::section()` / `Panel::extendTable()`:

```php
// package boot
Api::storefront('v1')->resource(Storefront\Resources\V1\ProductResource::class);

// an add-on
Api::storefront('v1')->extend(ProductResource::class, ReviewsProductExtension::class);
Api::storefront('v1')->resource(ReviewResource::class);
Api::admin('v1')->extend(Admin\Resources\V1\OrderResource::class, ErpOrderExtension::class);
```

`ResourceExtension` is the shape add-ons implement; it mirrors `Lunar\Panel\Tables\TableExtension` and `SectionExtension` so an add-on author learns one pattern:

```php
abstract class ResourceExtension
{
    abstract public function extends(): string;   // ProductResource::class
    public function fields(): array   { return []; }
    public function includes(): array { return []; }
    public function filters(): array  { return []; }
    public function sorts(): array    { return []; }
    public function routes(): ?Closure { return null; }   // extra endpoints under the resource prefix
    public function eagerLoad(): array { return []; }     // relations to load on index/show
}
```

Extensions compose: many add-ons can extend one resource, and later registrations cannot remove earlier fields (a field named twice is a boot-time exception). A host that needs to change a built-in resource's own fields replaces it: `Api::storefront('v1')->replace(ProductResource::class, MyProductResource::class)` where the replacement extends the built-in. Extensions are keyed by the built-in class, so they keep applying to the replacement. This closes the draft's open question H.5.

The registry is introspectable. `GET /{surface}/v1/_schema` returns every registered resource with its fields, includes, filters (and operators), sorts and routes, honouring the caller's abilities. `lunar:api:schema` prints the same. An OpenAPI document and a TypeScript client are generated from this later (follow-on, not in scope).

#### Query grammar

JSON:API's grammar, as in the draft, on both surfaces:

| Query | Effect |
| --- | --- |
| `?include=brand,variants.values` | eager-load and embed relationships |
| `?fields[products]=name,slug,prices` | sparse fieldset per type |
| `?filter[brand]=acme` / `?filter[price][gte]=1000` | registered filters, optional operator |
| `?sort=-price,name` | registered sorts |
| `?page[number]=2&page[size]=24` | page pagination (default) |
| `?page[cursor]=...` | cursor pagination where the resource opts in |

Unknown includes, fields, filters, sorts and operators are rejected with `422` listing the allowed values. Include depth is capped (default 3). Every include resolves through eager loading; `Model::preventLazyLoading()` stays on in the test suite so an N+1 fails a test.

#### Envelope

```jsonc
{
  "data": { "id": "01J9...", "type": "products", "name": "Acme Widget", "brand": { "id": "01J8...", "type": "brands", "name": "Acme" } },
  "meta": { "channel": "webstore", "currency": "GBP", "locale": "en" },
  "links": { "self": "..." }
}
```

Collections return `data` as an array with pagination in `meta` and `first`/`last`/`next`/`prev` in `links`. Related resources embed under the parent, not in a separate `included` array: the consumer ergonomics argument from the draft stands, and the duplication it allows is bounded by `?include=`. This is not strict JSON:API and does not claim to be; `Content-Type` is `application/json`.

Errors use the JSON:API error object (`status`, `code`, `title`, `detail`, `source.pointer` / `source.parameter`). One handler maps `ApiException` subclasses, Laravel validation, auth, authorisation, model-not-found and throttling onto it. Titles and details are translated strings shipped in the 16 locales.

### D. Authentication and authorisation

Two surfaces, two principals, and Lunar owns only the one it has a model for.

#### Storefront surface

- **Guests by default.** Catalogue reads and cart operations need no auth.
- **Customers via the host's guard.** `lunar.api.storefront.guard` names the guard for `/me` and any endpoint the child spec marks `protected`; `null` disables customer endpoints. Sanctum is the documented default because it is first-party and works for SPAs and native clients alike, but the package neither requires it nor issues customer tokens, login, registration or password reset. The authenticated user must implement `Lunar\Core\Contracts\LunarUser`; a `Contracts\CustomerResolver` (default: `latestCustomer()`) pushes the customer into `StorefrontSession`. Hosts bind their own resolver to change that.
- **Guest carts by signed token.** `X-Lunar-Cart` carries `base64url(cart public_id . '.' . expiry)` plus an HMAC over it keyed by the app key, so the token is stateless, unguessable and cannot be forged from a leaked `public_id`. `ResolveCart` middleware verifies it, loads the cart, and calls `CartSession::use($cart)` so every downstream `CartSession::current()` call works unchanged; on a missing header the first cart write creates a cart and returns the token in the body and the header. On authentication the middleware calls `CartSession::associate()` with the configured policy. No cookies by default; a same-origin host can swap `ResolveCart` for a cookie variant. This closes the draft's cart-token open question.
- **Context headers.** `X-Lunar-Channel`, `X-Lunar-Currency`, `Accept-Language` populate `StorefrontSession` through `ResolvesStorefrontContext`, with unknown codes rejected (`422`) and missing headers falling back to region and global defaults. Responses echo the resolved values in `meta` and in the same headers.

#### Admin surface

- **Lunar issues and owns admin API keys.** `Lunar\Api\Models\ApiKey`: `public_id`, `name`, `token_prefix` (first 8 chars, for display), `token_hash` (SHA-256; the plaintext is shown once), `abilities` (json), `staff_id` (nullable owner), `last_used_at`, `expires_at`, `revoked_at`. Bearer token in `Authorization`. A `lunar-api-key` auth driver and a `lunar-api` guard are registered by the package; `lunar.api.admin.guard` lets a host substitute its own guard (Passport, an SSO proxy) as long as the resolved user answers `can()`.
- **Abilities reuse the staff permission vocabulary.** Write abilities are the existing `Lunar\Core\Auth\Manifest` handles (`catalog:manage-products`, `sales:manage-orders`, ...). Read is gated per group by `catalog:read`, `sales:read`, `settings:read`. `*` grants everything. A `Gate::after` for the `lunar-api` guard resolves abilities from the key the same way the panel's gate resolves them from roles, so a policy or a `requires()` on a field works identically for a staff session and a key.
- **Attribution.** When a key has an owner, `spatie/laravel-activitylog` records that staff member as causer; an ownerless integration key records the key itself, so an ERP sync is distinguishable in the activity log from a human.
- **Issuance.** Slice 1 ships `lunar:api:key` (create, list, revoke) and the admin endpoints `/admin/v1/api-keys` (a key with `settings:manage-api-keys` can manage keys). A panel Settings section for keys and webhook endpoints is a separate panel spec.

Why own keys rather than Sanctum personal access tokens on `Staff`: Sanctum would add a dependency to core (the trait lives on the model), ties every key to a human staff account, and has no notion of read-only scopes without abilities we would define anyway. A dedicated model gives integrations a first-class actor, expiry, rotation and per-key webhook ownership (section E) for the cost of one table and one guard driver.

### E. Webhooks

Webhooks ship in [[0080-webhooks]], but the kernel decisions above are made for them, and the design is fixed here so the child spec fills in detail rather than reopening it.

- **Topics.** A `Topic` maps a core event to a stable dotted name and a payload builder. Built-ins cover the 0043 stream: `cache.invalidated` from every `CacheInvalidationEvent`, plus entity lifecycle topics (`product.created|updated|deleted`, `collection.*`, `brand.*`, `product_option.*`, `order.placed|cancelled|closed|reopened|refunded|payment_status_updated|fulfilment_status_updated`, `fulfilment.created|held|released|status_updated`, `customer.*`, `discount.*`). Add-ons register topics through `Api::webhooks()->topic(MyTopic::class)`, and any add-on event becomes deliverable.
- **Payloads reuse admin resources.** An entity topic's `data` is the admin v1 resource for that model, serialised through a `SerializationContext` built offline. `cache.invalidated` is deliberately lean: `{ tags, morph_type, id, reason }` straight from the event's captured scalars, so a delete or a queued delivery never rehydrates a row. That payload is what a storefront on Vercel feeds to `revalidateTag()`, or a CDN to a surrogate-key purge.
- **Prerequisite in core.** `InvalidatesCache::cacheKey()` still returns the integer key, so tags read `product:123`. 0043 built that seam so the scheme could move to `public_id` once it existed. It moves before webhooks ship: tags become `product:01J9...`, which is what an external consumer can correlate with API responses. `CacheTags::for()` and existing listeners are unaffected because they never parse the tag.
- **Storage and delivery.** `webhook_endpoints` (`public_id`, `url`, `secret`, `topics` json, `active`, `api_key_id` nullable, `description`, `consecutive_failures`) and `webhook_deliveries` (`endpoint_id`, `topic`, `payload`, `status`, `attempts`, `response_status`, `next_attempt_at`). A listener on each topic's event writes one delivery per subscribed endpoint after commit and dispatches a queued `DeliverWebhook` job; retries back off exponentially to a configurable cap, and an endpoint auto-disables after N consecutive failures. Deliveries are `Prunable`.
- **Signing.** `X-Lunar-Signature: t=<unix>,v1=<hmac-sha256(t . '.' . body, secret)>`, `X-Lunar-Topic`, `X-Lunar-Delivery` (the delivery `public_id`, for idempotent consumers), `X-Lunar-Version`. The verification recipe with a timestamp tolerance is documented; a `Webhooks::verify()` helper is offered for Laravel consumers.
- **Management** through the admin surface (`/admin/v1/webhook-endpoints`, with a `test` verb and a deliveries sub-resource) and `lunar:api:webhook`. This dogfoods the admin API before any UI exists.

### F. Surfaces in scope for v1

Each surface's controllers are thin: parse the grammar, resolve the resource, call a model verb or an action contract, serialise. No business logic lives in the API package; where a needed operation has no core action, the action is added to core with a contract and `execute()`, exactly as the panel spec requires.

**Storefront v1** ([[0078-storefront-api]]): the draft's catalogue reads (products, collections, collection groups, brands, URL resolve), cart (lines, coupons, addresses, shipping options), checkout (order creation via `Cart::createOrder()`; payment through `PaymentType::authorize()` with driver data in the body), and the customer area (`/me`, addresses, orders). Payment-intent creation is driver-specific, so a driver registers its own storefront routes through `ResourceExtension::routes()`: the Stripe package adds `POST /checkout/stripe/intent` calling `StripeManager::createIntent()`. The API package knows no driver by name.

Deltas the child spec must absorb from the draft: `id` is `public_id`; one package with surface namespaces rather than `lunar/api` alone; the class-based `Resource` / `ResourceExtension` model from section C in place of the closure-only facade builder; cart token and H.5 resolved as above; search is a filter the search package registers (below); a `_schema` endpoint exists.

**Admin v1** ([[0079-admin-api]]): reads and writes for catalogue (products, variants, prices, options and values, collections and groups, brands, product types, attributes and groups, tags, URLs, media), sales (orders with verb endpoints `cancel`, `capture`, `refund`, `notify`, `close`, `reopen`; fulfilments with `ship`, `fulfil`, `hold`, `release`, `split`, `merge`, tracking; customers and addresses; discounts), inventory (locations, stock levels via `AdjustsStock`, movements read-only), and settings (channels, currencies, languages, regions, countries read-only, tax classes and zones, customer groups). Every write calls the matching `Contracts\Actions\*` binding. Verbs are `POST /{resource}/{id}/{verb}` sub-resources, not status patches, so the state machines stay the only path. Admin writes accept an `Idempotency-Key` header, honoured for 24 hours.

**Search.** `lunarphp/search`, when installed, registers a `ResourceExtension` on the storefront products resource adding `Filter::make('search', ...)` that routes through the bound engine and returns hits in engine order (with `Sort::make('relevance')`). Without the search package the filter is absent and `_schema` says so.

### G. Package plumbing

- Monorepo: `packages/api` is picked up by `monorepo-builder.php` automatically; add the PSR-4 entries and `lunarphp/api: self.version` to the root `composer.json`; add the `api` testsuite to `phpunit.xml` and to the CI matrix in `.github/workflows/tests.yml`.
- Config: `config/api.php` merged under `lunar.api` (`storefront.prefix|enabled|guard|register_routes|middleware`, `admin.prefix|enabled|guard|register_routes|middleware`, `pagination`, `webhooks.retry|max_failures|prune_after_days`).
- Translations: `resources/lang/{16}` for error strings and console output, English first, each locale translated.
- Migrations: the package ships one baseline migration per table it owns; core's baseline is untouched.
- Docs: a new "API" section in the 2.x docs (storefront guide, admin guide, extending the API, webhooks, auth recipes for Sanctum), and an upgrade note mapping v1 API endpoints to v2 storefront equivalents (manual; no Rector rule crosses the HTTP boundary).

## Alternatives considered

- **Separate packages per surface (`api-storefront`, `api-admin`, `webhooks`).** Splits the kernel before it exists and forces webhooks to depend on admin for payloads. Rejected for now; the namespace layout keeps a later split cheap.
- **Ship the API inside core.** Every consumer would carry routes, middleware and guards they may not want. Rejected, as in the draft.
- **Sanctum personal access tokens on `Staff` for admin.** Adds a Sanctum dependency to core, gives integrations no actor of their own, and needs an abilities scheme regardless. Rejected in favour of a Lunar-owned `ApiKey`; Sanctum remains the documented default for storefront customers because Lunar does not own customer accounts.
- **Laravel `JsonResource` as the resource base.** Bound to the request; unusable for webhook payloads and console output without shims. A thin own abstraction with the same feel is cheaper than fighting it.
- **`spatie/laravel-query-builder` for the grammar.** A good library, but it has no per-surface registry, no introspection for `_schema`, and no extension keyed by resource class; we would wrap it and own the wrapper. Rejected to avoid a dependency that adds little once the registry exists.
- **GraphQL.** Strongest fit for embedded relationships and free introspection, but a heavy runtime dependency and a harder add-on story (schema fragments plus resolvers). Rejected for v1; a `lunarphp/graphql` add-on could read the same registry later.
- **Strict JSON:API.** Grammar kept, `included` stitching dropped, for the reasons in the draft.
- **Header versioning.** Path versioning is visible in logs, CDNs and browser tabs and costs nothing. Rejected.
- **Do nothing.** Every Lunar storefront and integration stays bespoke at the wire; the headless framing is not true.

## Migration impact

- **Database migrations:** three new tables owned by the API package. No change to core's baseline.
- **Core changes (small, prerequisite):** `InvalidatesCache::cacheKey()` returns `public_id` where the model has one; a `settings:manage-api-keys` permission and read abilities `catalog:read`, `sales:read`, `settings:read` join the `Auth\Manifest` base permissions so the panel's roles screen and the API share one vocabulary. Both are additive.
- **Breaking changes to the public contract surface:** none. New contract surface: `Lunar\Api\Facades\Api`, `Resource`, `ResourceExtension`, `Field`, `Embed`, `Filter`, `Sort`, `SerializationContext`, `Topic`, the `X-Lunar-*` headers and the envelope. Locked per surface version once shipped.
- **Upgrade path for v1.x consumers:** documented endpoint mapping; manual. The notes state up front that `/api/storefront/v1/` is the first version of a new contract and is not compatible with the Lunar v1.x API.
- **Translation / locale impact:** new `resources/lang` in the package, 16 locales.
- **Filament / admin impact:** none. A panel Settings section for API keys and webhook endpoints is a follow-on panel spec.
- **Long-lived workers:** `ApiManager` is stateless after boot (singleton). Anything holding the current principal, cart or context is `scoped`; `tests/core/Unit/ServiceLifetimesTest.php` gets the new entries.

## Open questions

- **Read abilities' home.** The proposal adds `catalog:read`, `sales:read`, `settings:read` to core's `Auth\Manifest`. The alternative is API-only abilities registered by the package, keeping core's list untouched but forking the vocabulary. Decide in review of this spec.
- **Per-resource policies.** `Field::requires()` and route-level abilities cover v1. Whether resources also declare a policy class (for row-level rules such as customer-group-gated catalogue) is decided when B2B catalogue gating is specced.
- **Admin write validation.** The panel's form requests are Inertia-shaped and cannot be reused as-is. Whether rule sets move into core `Validation/` so both admin UIs and the API share them is a candidate refactor for [[0079-admin-api]], not a blocker.
- **Rate limiting defaults.** `throttle:api` on both surfaces to start; separate read/write budgets when there is a complaint.
- **Cursor pagination on the storefront by default.** Page-based is the draft's default; cursor is cheaper for large catalogues. Decide per resource in [[0078-storefront-api]].
- **OpenAPI and a TypeScript client generated from `_schema`.** Worth doing; separate small spec once the registry is stable.

## References

- `specs/draft-storefront-api.md` — the storefront draft this spec generalises; becomes [[0078-storefront-api]].
- [[0040-storefront-context]] — `StorefrontContext` and `ResolvesStorefrontContext`, driven by the storefront middleware.
- [[0042-model-query-builders]] — registered scopes, reused by `Filter::scope()`.
- [[0043-cache-invalidation-and-events]] — the event stream webhooks deliver; its deferred "outbound webhooks" follow-on.
- [[0044-storefront-cache-tagging]] — the tag vocabulary `cache.invalidated` carries.
- [[0046-public-id-external-addressing]] — external ids used throughout.
- [[0049-inertia-panel]] — extension conventions (`SectionExtension`, `TableExtension`) the `ResourceExtension` mirrors; staff guard and permission gate the admin surface reuses.
- [[0064-scoped-service-lifetimes]] — lifetime rules for the new request-stateful services.
- `Lunar\Core\Contracts\{StorefrontSession, CartSession, LunarUser, PaymentType}` and `Contracts\Actions\*` — the seams every endpoint calls.
- JSON:API 1.1 — borrowed query grammar and error object.

## Implementation plan

Slices owned by this spec; the child specs carry their own.

- [ ] Slice 0 — Write and land [[0078-storefront-api]], [[0079-admin-api]], [[0080-webhooks]] against this spec; retire `specs/draft-storefront-api.md` into 0078.
- [x] Slice 1 — Package skeleton: `packages/api`, service provider, config, middleware groups, root `composer.json` autoload, `api` testsuite in `phpunit.xml` and CI, 16-locale `resources/lang` scaffold.
- [x] Slice 2 — Kernel: `Resource`, `Field`, `Embed`, `Filter`, `Sort`, `SerializationContext`, `QueryParser` and appliers, envelope and error handler, `public_id` route binding, `ApiManager` with `resource()` / `extend()` / `replace()`, `_schema` endpoint and `lunar:api:schema`. Unit-tested against a fixture resource; strict lazy loading on.
- [x] Slice 3 — Core prerequisites: `cacheKey()` on `public_id`; `settings:manage-api-keys` and read abilities in `Auth\Manifest`.
- [ ] Slice 4 — Storefront surface (per 0078): context and cart-token middleware, customer resolver, then catalogue, cart, checkout, customer area. *Shipped ahead of 0078 to prove the kernel end to end: context headers, cart token, customer resolver, catalogue reads (products, brands, collections, collection groups), `GET /cart`, `POST /cart/lines`, `GET /me`. Remaining cart writes, checkout and the customer area's addresses and orders follow 0078.*
- [x] Slice 5 — Admin auth: `ApiKey` model and migration, guard driver, abilities gate, activity-log attribution, `lunar:api:key`, `/admin/v1/api-keys`.
- [ ] Slice 6 — Admin surface (per 0079): catalogue, then sales, inventory, settings; idempotency keys. *Products read-only shipped as the proving resource.*
- [ ] Slice 7 — Webhooks (per 0080): topics, endpoints, deliveries, signing, retry, management endpoints and command.
- [ ] Slice 8 — Search filter extension in `lunarphp/search`; Stripe storefront intent route in `lunarphp/stripe`; docs and upgrade notes.
