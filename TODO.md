# TODO List for Lunar v2

## Approach

Each item below should have a written spec in `specs/` (alongside this file in the Lunar package) before implementation begins. One markdown file per item, named `NNNN-short-slug.md`, starting from `specs/0000-template.md`. See `specs/README.md` for the full convention.

## Outstanding

- Support `Model::preventLazyLoading()`
- Ensure all service-layer classes are DI'd
- Add events, including specific events for cache invalidation
- Add `public_id` (ULID) to externally-addressable models
- Add `name` and `description` dedicated fields
- Change `compare_price` to `list_price`
- Price data type performance changes
- Attributes remodel to simplify data and allow for re-use
- Implement state machines, replacing soft-deletes
    - Specifically, products (draft, published, archived) & orders (payment, fulfilment and order status)
- `StorefrontContext` for CartSessionManager and other services
- Region concept to define channel, currency, language, tax_zone, countries and price display
- Add Vendors concept to support marketplace developments
- Make order line purchasables optional
- Stop storing shipping options as polymorphic purchasables on cart/order lines
    - Currently `ShippingOption` is a non-Eloquent value object from `ShippingManifest`, yet lines morph to it via a hardcoded `purchasable_id => 1` (see `CreateShippingLine`)
    - Drop the fake morph: rely on the existing `type = 'shipping'` column and store the option `identifier` + a snapshot of name/price/meta directly on the line
    - Make `purchasable_type`/`purchasable_id` nullable (ties in with "Make order line purchasables optional")
    - Resolve the live `ShippingOption` from `ShippingManifest` by identifier when needed, rather than via the polymorphic relation
- Cart/order line grouping
- Split out Promotions concept from Discounts
- Add cart totals caching in the database
- Add Boost guidelines to packages


## Ideas

- Inventory
- RMA
- Admin MCP
- Storefront MCP
- Developer MCP

## Done

- Add an Upgrade package for those migrating from v1.x (using Rector) — land first so subsequent breaking specs can ship their Rector rules and data migrations into it
- Flatten v1.x migrations into a v2 baseline — single flat set of migration files at v2.0.0; upgrade package handles the v1 → v2 schema transformation and rewrites the `migrations` ledger
- Change to `\Lunar\Core` namespace
- Filament v5 upgrade (spec 0004)
- Filament v5 schemas refactor (spec 0005)
- Extract `lunarphp/filament` bridge package and reshape the install model (spec 0006)
- Pages refactor — inline the 10 page-extension traits into the 5 base page classes (spec 0007)
