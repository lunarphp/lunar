# TODO List for Lunar v2

## Outstanding

- Change to `\Lunar\Core` namespace
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
- Filament v5 upgrade
- Add Boost guidelines to packages
- Move core Filament e-commerce components to a new `lunarphp/filament` package
- Add an Upgrade package for those migrating from v1.x (using Rector)

## Ideas

- Inventory
- RMA
- Admin MCP
- Storefront MCP
- Developer MCP

## Done

Nothing to show!
