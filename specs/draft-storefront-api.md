# Draft — Storefront REST API

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: API platform (spec 0077). This draft is the basis for spec 0078; see 0077 section F for the deltas it must absorb.

## Problem

Lunar v2 has no first-party HTTP API. A headless storefront has to either (a) talk to Lunar through the host Laravel app's controllers, which every consumer reinvents, or (b) reach into Lunar's Eloquent models directly from inside the same monolith. Neither serves the "headless commerce" framing that's been a v2 goal since the migrations were flattened.

Concrete gaps as of this spec:

- **No canonical surface.** Every Lunar consumer who wants a JS frontend ends up writing their own controllers around `Lunar\Core\Models\Product`, their own paginator, their own filter parser, their own price-with-tax projection — all of which already exist conceptually inside Lunar (storefront session, cart pipeline, tax driver). The duplication wastes consumer time and means two Lunar stores can ship two incompatible product payloads.
- **No extension story for add-ons.** A reviews add-on can publish migrations, models, and Filament resources, but has no agreed mechanism to make `GET /products/{id}` return `average_rating` or `?include=reviews`. Today each add-on either ships its own parallel API or asks consumers to extend a published controller — both options fragment the surface.
- **Channel/currency context isn't reachable over HTTP.** `Lunar\Core\Contracts\StorefrontSession` already owns the channel/currency/customer-group resolution that the cart pipeline depends on. Without an API layer, every consumer writes their own middleware to populate it from request headers.
- **The cart pipeline isn't exposed.** `Lunar\Core\Contracts\CartSession` and the cart modifier chain are the actual contract for "what happens when you add a line" — but the only callers today are the (deprecated) classic storefront stub and Filament. Headless consumers re-derive cart write semantics, often missing modifiers.
- **v1 had a storefront API and people relied on it.** v2 dropped it during the rebuild. The upgrade story currently asks v1 consumers to write Laravel controllers from scratch.

Net effect: Lunar v2 is "headless-ready" at the data layer but not at the wire. This spec proposes the wire.

## Proposal

A new sub-package `lunar/api` exposing a versioned, RESTful storefront API. Resources are Laravel API Resources with a JSON:API-inspired query grammar (`?include=`, `?fields[type]=`, `?filter[key]=`, `?sort=`, `?page[...]=`). Authentication is delegated to the host app, with Sanctum the documented default. Add-ons extend the surface through a `Lunar\Api\Facades\Api` registry that supports new endpoints, new relationships on built-in resources, new attributes on built-in resources, and new filters/sorts.

Admin is **out of scope** for this spec but the design avoids closing the door on a sibling admin surface later.

### A. Package layout

```
packages/api/
├── composer.json                      lunar/api, requires lunar/core
├── config/api.php                     prefix, versions, default guard, …
├── routes/v1.php                      route definitions, grouped by resource
└── src/
    ├── ApiServiceProvider.php
    ├── Facades/Api.php                facade for the resource registry
    ├── Http/
    │   ├── Controllers/V1/…           one per built-in resource
    │   ├── Middleware/
    │   │   ├── ResolveStorefrontSession.php
    │   │   ├── ResolveCart.php
    │   │   └── EnforceJsonHeaders.php
    │   ├── Requests/                  form requests for write endpoints
    │   └── Resources/V1/              one per resource (Product, Cart, …)
    ├── Query/
    │   ├── QueryParser.php            parses include/fields/filter/sort/page
    │   ├── IncludeRegistry.php
    │   ├── FilterRegistry.php
    │   └── SortRegistry.php
    ├── Registry/
    │   ├── ApiManager.php             the thing the facade resolves to
    │   ├── ResourceBuilder.php        fluent definition object
    │   └── ResourceDefinition.php     materialised registration
    └── Exceptions/
        ├── ApiException.php
        └── ValidationException.php
```

The package opt-in is `composer require lunar/api` plus a one-line route registration the host app drops into its own `routes/api.php` (or that the service provider does by default — see §B). No Filament dependency. No database migrations in this spec — the API reads existing tables and writes through existing pipelines.

### B. Routing & versioning

URL prefix carries the version: `/api/lunar/v1/...`. Configurable via `config('api.prefix', 'api/lunar')` and `config('api.version', 'v1')`. Multiple versions can coexist by loading multiple route files; v1 is the only one shipped here.

The service provider loads `routes/v1.php` automatically under the configured prefix, applying the package's middleware group (`api`, `lunar.storefront-session`, `lunar.cart`, plus the configured auth middleware where applicable). Hosts that want full control set `config('api.auto_register_routes', false)` and `require` the route file themselves.

A breaking change to a built-in resource cuts a new version directory (`Http/Controllers/V2`, `Http/Resources/V2`, `routes/v2.php`). Add-ons publish per-version extensions; the registry namespaces them by version (`Api::version('v1')->resource('products', …)`). Default version is the latest, with an explicit prefix per registration.

### C. Authentication

The package itself ships no guard configuration. `config/api.php` declares two middleware lists:

```php
'middleware' => [
    'public'     => ['throttle:api'],              // routes that allow guests
    'protected'  => ['throttle:api', 'auth:sanctum'], // routes that require a user
],
```

Sanctum is the documented default — `composer require laravel/sanctum` + the standard config. The README walks through it. Hosts that want Passport, an OAuth proxy, or a custom JWT guard swap `auth:sanctum` for their middleware in `config/api.php`. Every controller declares which list it belongs to (`public` for reads of public catalog, `protected` for `/cart`, `/checkout`, `/me`, …); the package never reaches into auth internals.

### D. Request context (channel, currency, locale)

A middleware `ResolveStorefrontSession` populates the existing `Lunar\Core\Contracts\StorefrontSession` from request headers, with graceful fallback to defaults:

| Header                | Drives                  | Fallback                                  |
|-----------------------|-------------------------|-------------------------------------------|
| `X-Lunar-Channel`     | `setChannel(...)`       | `Channel::getDefault()`                   |
| `X-Lunar-Currency`    | `setCurrency(...)`      | channel's default currency                |
| `Accept-Language`     | response translations   | channel's default language                |
| (auth subject)        | `setCustomer(...)`      | guest                                     |

Unknown channel/currency codes produce a `422 Unprocessable Entity` rather than silent fallback — silent fallback hides misconfiguration. Missing headers always fall back. Responses echo the resolved values back in `X-Lunar-Channel` and `X-Lunar-Currency` headers so clients can confirm what the server actually used.

### E. Customer resolution

The package assumes the host's authenticated user implements `Lunar\Core\Contracts\LunarUser` (i.e. has `customers()`, `latestCustomer()`, `orders()` — already a v2 contract). A `Lunar\Api\Auth\CustomerResolver` reads `$request->user()->latestCustomer()` and pushes it into the `StorefrontSession`. Hosts that bind their own `CustomerResolver` swap the behaviour without touching middleware.

Lunar does **not** issue its own customer tokens, run its own login/register endpoints, or own the password reset flow. Those belong to the host app. The README links to a recipe showing Sanctum + a `HasCustomer` trait on the User model.

### F. Response shape

Laravel API Resources wrapped in a fixed envelope:

```jsonc
// GET /api/lunar/v1/products/123?include=brand,variants
{
  "data": {
    "id": "123",
    "type": "products",
    "name": "Acme Widget",
    "slug": "acme-widget",
    "prices": [ { "currency": "GBP", "price": 1999, "formatted": "£19.99" } ],
    "brand": { "id": "7", "type": "brands", "name": "Acme" },
    "variants": [ /* nested, not in a separate `included` array */ ]
  },
  "meta": { "channel": "webstore", "currency": "GBP" },
  "links": { "self": "https://shop.test/api/lunar/v1/products/123" }
}
```

For collections: `data` is an array, `meta` adds pagination, `links` adds `first`/`last`/`next`/`prev`.

This is **not** strict JSON:API. We borrow its query grammar (next section) but embed related resources directly under the parent, so a client can do `response.data.brand.name` with no stitching. The tradeoff is acknowledged: nested embedding can duplicate a related record across siblings (two products with the same brand → two copies of the brand). The duplication is bounded by `?include=` choices and the response is gzipped on the wire — the consumer-ergonomics win is worth the size cost. Add-ons that need a JSON:API-strict mode can register their own response transformer.

### G. Query grammar

| Query                                    | Effect                                                |
|------------------------------------------|-------------------------------------------------------|
| `?include=brand,variants.values`         | eager-load + embed nested relationships               |
| `?fields[products]=name,slug,prices`     | sparse fieldset on the products resource              |
| `?fields[brands]=name`                   | sparse fieldset on an included relationship type      |
| `?filter[brand]=acme`                    | exact-match filter                                    |
| `?filter[price][gte]=1000`               | operator-suffixed filter (`gte`, `lte`, `like`, `in`) |
| `?sort=-price,name`                      | comma-sep list, leading `-` = descending              |
| `?page[number]=2&page[size]=24`          | page-based pagination (default)                       |
| `?page[cursor]=eyJpZCI6MTIzfQ`           | cursor pagination on endpoints that opt in           |

Every available include / filter / sort lives in a per-resource registry (§H), so unknown keys are rejected with a `422` and a list of allowed keys. This makes the contract introspectable — a `GET /api/lunar/v1/products?_describe=1` could later return the registered keys, but that's out of scope for this spec.

Default page size is `15`, max `100`, both per-resource overridable.

### H. Resource registry & extensibility

The heart of the spec. A single facade owns every endpoint definition.

```php
use Lunar\Api\Facades\Api;
use Lunar\Api\Http\Resources\V1\ProductResource;
use Lunar\Core\Models\Product;

// Built-in registration, done by the package's own service provider:
Api::resource('products', ProductResource::class)
    ->model(Product::class)
    ->includes(['brand', 'collections', 'variants', 'variants.values'])
    ->filters([
        'brand'     => Filters\BrandFilter::class,
        'collection'=> Filters\CollectionFilter::class,
        'price'     => Filters\PriceFilter::class,         // supports gte/lte
        'attribute' => Filters\AttributeFilter::class,
    ])
    ->sorts(['name', 'price', 'created_at'])
    ->routes(function (ApiRouter $router) {
        $router->get('/',     [ProductController::class, 'index']);
        $router->get('/{id}', [ProductController::class, 'show']);
    });
```

The facade resolves to `Lunar\Api\Registry\ApiManager`. Each `->resource(...)` call returns a `ResourceBuilder` that records includes, filters, sorts, and route closures. At boot, after all service providers have registered, `ApiManager::routes()` walks the registry and emits the actual Laravel routes (delegating to standard `Route::group(...)` under the configured prefix and middleware).

**Add-ons extend in four ways:**

#### H.1 Register new endpoints

```php
// in lunar/reviews ServiceProvider::boot()
Api::resource('reviews', ReviewResource::class)
    ->model(Review::class)
    ->filters(['product_id' => Filters\ExactFilter::class, 'rating' => Filters\NumericFilter::class])
    ->sorts(['rating', 'created_at'])
    ->routes(function (ApiRouter $router) {
        $router->get('/',     [ReviewController::class, 'index']);
        $router->get('/{id}', [ReviewController::class, 'show']);
        $router->post('/',    [ReviewController::class, 'store'])->middleware('protected');
    });
```

#### H.2 Add relationships to built-in resources

```php
Api::resource('products')->extend(function (ResourceBuilder $builder) {
    $builder
        ->include('reviews', function (Product $product) {
            return $product->reviews()->latest()->limit(10);
        }, ReviewResource::class)
        ->include('reviews_summary', fn (Product $p) => $p->reviews_summary, ReviewSummaryResource::class);
});
```

`?include=reviews` then returns reviews nested under each product. The include callback is the relationship resolver — by default it's `$model->{$name}()`, but the explicit form (above) lets the add-on shape the relationship (limit, ordering, scopes) without modifying the model. Sparse fieldsets on includes (`?fields[reviews]=rating,body`) are honoured through the registered resource class.

#### H.3 Add attributes to built-in resources

```php
Api::resource('products')->extend(function (ResourceBuilder $builder) {
    $builder
        ->attribute('average_rating', fn (Product $p) => $p->average_rating ?? 0)
        ->attribute('review_count',   fn (Product $p) => $p->reviews_count);
});
```

Attributes appear at the top level of the resource payload. They're opt-out via `?fields[products]=...` like any built-in attribute. The closure runs in the resource serialisation phase, so `$p` is the model instance the resource is wrapping — N+1 prevention is the add-on's job (use `->load(...)`, a `withCount`, or a relationship registered in the index controller).

#### H.4 Register filters and sorts

```php
Api::resource('products')->extend(function (ResourceBuilder $builder) {
    $builder
        ->filter('min_rating', function (Builder $query, $value) {
            $query->where('reviews_avg_rating', '>=', (float) $value);
        })
        ->sort('rating', fn (Builder $q, string $direction) => $q->orderBy('reviews_avg_rating', $direction));
});
```

`?filter[min_rating]=4` and `?sort=-rating` then work on the products endpoint with no controller change.

#### H.5 Swapping a built-in resource entirely

Out of scope for the registry. A consumer who needs to replace `ProductResource` with their own class binds their resource via `Api::resource('products')->setResourceClass(MyProductResource::class)`. They lose the registered includes/attributes added by other add-ons unless their replacement re-applies them — explicitly the consumer's choice.

### I. Built-in resources (v1)

The package ships with the following endpoints. Each is a thin controller delegating to the model + the registered query grammar; no business logic lives in the API package itself.

**Catalog (read, public):**
- `GET /products`, `GET /products/{id}` — id, slug, sku, or `?filter[slug]=`
- `GET /collections`, `GET /collections/{id}`
- `GET /collection-groups`, `GET /collection-groups/{id}`
- `GET /brands`, `GET /brands/{id}`
- `GET /urls/resolve?path=/some-slug` — slug→entity lookup so the storefront can route

**Cart (read/write, public — cart token identifies guest carts):**
- `GET /cart` — the current cart resolved from `X-Lunar-Cart` header or session
- `POST /cart/lines` — add a line
- `PATCH /cart/lines/{id}` — update qty / meta
- `DELETE /cart/lines/{id}`
- `POST /cart/coupons` — apply a coupon
- `DELETE /cart/coupons/{code}`
- `POST /cart/shipping-address`, `POST /cart/billing-address`
- `GET /cart/shipping-options`, `POST /cart/shipping-option`

**Checkout (write, public or protected depending on flow):**
- `POST /checkout` — converts the cart to an order via the existing cart pipeline; returns the order resource
- `POST /checkout/payment-intent` — driver-delegated payment intent creation

**Customer area (protected):**
- `GET /me`, `PATCH /me`
- `GET /me/addresses`, `POST /me/addresses`, `PATCH /me/addresses/{id}`, `DELETE /me/addresses/{id}`
- `GET /me/orders`, `GET /me/orders/{id}`

Every write goes through the existing pipelines / cart manager / order reference generator — the API is a wire format, not a second business-logic implementation.

### J. Guest carts

A guest cart is identified by an opaque signed token returned in the response body and in an `X-Lunar-Cart` header on the first cart request. Subsequent requests echo the token back in `X-Lunar-Cart`; the middleware (`ResolveCart`) loads the cart via `CartSession::use(...)`. On successful authentication, the cart is associated to the user via the existing `CartSession::associate(...)` contract — no new code, the middleware just calls the contract.

Cookies are **not** used by default. Cookies tie the API to the storefront's domain and break native mobile / cross-origin SPA clients. Hosts that want cookie-based carts (because their storefront is same-origin) can swap the middleware for a cookie variant — same `CartSession` underneath.

### K. Errors

Standard envelope, JSON:API-shape borrowed because it's the only widely-known JSON error convention:

```json
{
  "errors": [
    {
      "status": "422",
      "code":   "invalid_filter",
      "title":  "Unknown filter",
      "detail": "filter[foo] is not a registered filter for the products resource.",
      "source": { "parameter": "filter[foo]" }
    }
  ]
}
```

Validation errors (form requests) map each field to one error object, with `source.pointer` for body fields and `source.parameter` for query/route. The package's `ApiException` and its subclasses (`ValidationException`, `NotFoundException`, `AuthenticationException`, `AuthorizationException`, `RateLimitException`) render to this shape via a single exception handler registered in the service provider.

### L. Translations & locale

Each resource that wraps a translatable model honours `Accept-Language`. The package sets the request locale via `app()->setLocale(...)` in middleware, with fallback to the resolved channel's default language and then the app default. Translations come from existing Lunar `Translation` rows / attribute data — the API does no translation itself.

The package's own strings (error `title` / `detail`) ship in all 16 Lunar locales (`ar, bg, de, en, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi`) at boot, English first then mirrored — same convention as every other sub-package.

## Alternatives considered

- **GraphQL.** Strongest fit for the "embed relationships directly" use case, plus introspection comes free. Rejected for v1 because (a) Lunar's consumer base is overwhelmingly REST-first, (b) GraphQL adds a hard runtime dependency (lighthouse / GraphQLite / etc.) that we'd own forever, (c) the extension story for add-ons is harder in GraphQL — every add-on has to publish schema fragments and resolvers, not just service-provider hooks. A separate `lunar/graphql` add-on could later sit alongside, sharing the resource registry; this spec doesn't preclude it.
- **Strict JSON:API.** Considered. Rejected on the consumer-ergonomics ground discussed in §F — `included` stitching is fine when the consumer is a JS client library, painful when it's a static-site generator or a curl-driven backend. The query grammar from JSON:API is the bit consumers find pleasant; the response shape is the bit they find friction. We take the grammar and drop the shape.
- **One sub-package per concern (api-storefront, api-admin, …).** Rejected for now: it splits the registry and the route boot logic before we know whether admin is going to land. If admin ships, this package can be carved up — but only after the registry has been exercised by real add-ons.
- **Ship the API inside `lunar/core`.** Considered. Rejected because (a) the package always ships even for consumers who only want Filament, (b) the HTTP layer is the right place to break out as a separate composer dependency, (c) it keeps the core lean of routing/middleware/controllers it doesn't own.
- **Reuse v1's API verbatim.** v1's API was Spark/Vue-shaped and tied to specific cart pipelines that no longer exist. A direct port would constrain the v2 contract to v1 shapes that aren't worth preserving. The upgrade package gets a notes section pointing v1 consumers at the new endpoints; field-level mapping is out of scope for this spec.
- **Do nothing.** Consumers continue to write their own controllers. Tolerable for power users, fatal for the "headless commerce" framing — it means every Lunar storefront is bespoke at the wire layer. The spec exists because the gap is the most-cited reason consumers stay on v1 + a third-party API layer.

## Migration impact

- **Database migrations**: none in v1 of this package. Cart token storage reuses the existing carts table (the `cart_session_id`-style column, if present; otherwise the signed token contains the id directly). Confirm during implementation.
- **Public contract surface**: net-additive. The new contracts are `Lunar\Api\Facades\Api`, the `ResourceBuilder` fluent API, and the per-resource registries (`IncludeRegistry`, `FilterRegistry`, `SortRegistry`). Once add-ons start depending on `Api::resource('products')->extend(...)`, that signature becomes a contract — locked behind the `v1` namespace for the lifetime of v1 endpoints.
- **Breaking changes to existing Lunar code**: none expected. The API consumes existing models, the existing `StorefrontSession`/`CartSession` contracts, the existing cart pipeline, the existing order reference generator. If implementation finds a contract gap (e.g. `StorefrontSession` missing a setter), that lands as a separate spec / PR.
- **Upgrade path for v1.x consumers**: documented in the upgrade package's notes — v1 API endpoint → v2 equivalent, with the caveat that response shapes have changed. No Rector rule is feasible across the HTTP boundary; this is a manual update.
- **Translation / locale impact**: the package ships its own `resources/lang/*` directory with the 16-locale convention, English first.
- **Filament / admin impact**: none. The API package has no Filament dependency.

## Open questions

- **Cart token format.** A short signed JWT-ish blob (`base64url(json({cart_id, exp}))` + HMAC) is the simplest. An opaque random string mapped to a row in a `cart_sessions` table is also reasonable and avoids the "stateless token can outlive a deleted cart" problem. Decide during implementation; the wire contract (`X-Lunar-Cart` header) is identical either way.
- **Whether to ship a TypeScript SDK or OpenAPI document in v1.** Both are worth doing eventually. Neither is on the critical path for the spec — the API contract is described here, and an OpenAPI doc can be generated from the registry in a follow-up. Worth a separate, smaller spec.
- **Rate limiting defaults.** Documented as `throttle:api` (Laravel's default 60/min). Should the package ship its own `throttle:lunar-storefront` group with separate budgets for read vs. write? Defer until we have a complaint.
- **Search integration.** `lunar/search` and `lunar/meilisearch` already exist. Should `/products?filter[search]=foo` route through `Search::search(...)` when a driver is bound, falling back to a `WHERE name LIKE` otherwise? Likely yes, but the seam belongs in `lunar/search` not here — open question for the search package owner.
- **CORS.** Out of the box, the host's `config/cors.php` covers the configured route prefix. The README spells this out. No package config beyond that.
- **Replace-resource-entirely.** §H.5 leaves consumers responsible for re-applying add-on extensions. Is there a better story (a `merge` mode that re-runs registered extensions against the new resource class)? Worth considering once at least one real consumer needs to swap a built-in resource.
- **Per-resource policies.** Should the registry support `->policy(ProductPolicy::class)` so authorization is declared alongside the resource? Probably yes, but reads of public catalog don't need it on day one; revisit when admin or B2B (customer-group-gated catalog) endpoints land.

## References

- `Lunar\Core\Contracts\StorefrontSession` — channel/currency/customer-group resolver the API middleware drives.
- `Lunar\Core\Contracts\CartSession` — cart resolution and association the API middleware drives.
- `Lunar\Core\Contracts\LunarUser` — the User-side contract the customer resolver reads.
- Spec [[0013-base-directory-reorganisation]] — folder responsibilities this package follows (Resources under `Http/Resources/V1`, contracts as plain interfaces under `Contracts`, etc.).
- Spec [[0006-filament-bridge-package.md]] — sibling pattern of "lunar/* sub-package consumes core, ships its own service provider, opt-in dependency".
- JSON:API 1.1 spec — borrowed query grammar (`include`, `fields`, `filter`, `sort`, `page`).
- Lunar v1 storefront API — prior art; not directly carried forward (see Alternatives).
