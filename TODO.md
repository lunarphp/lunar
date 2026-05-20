# TODO List for Lunar v2

## Outstanding

- Change to `\Lunar\Core` namespace
- Add `public_id` (ULID) to externally-addressable models
- Add `name` and `description` dedicated fields
- Change `compare_price` to `list_price`
- Implement state machines, replacing soft-deletes
    - Specifically, products (draft, published, archived) & orders (payment, fulfilment and order status)
- Add cart totals caching in the database
- Price data type performance changes
- Split out Promotions concept from Discounts
- Add Boost guidelines to packages
- Add an Upgrade package for those migrating from v1.x (using Rector)
- Filament v5 upgrade
- Move core Filament e-commerce components to a new `lunarphp/filament` package
- Add Vendors concept to support marketplace developments
- Attributes remodel to simplify data and allow for re-use
- Region concept to define channel, currency, language, tax_zone, countries and price display
- Add events, including specific events for cache invalidation
- Make order line purchasables optional
- Support `Model::preventLazyLoading()`
- Ensure all service-layer classes are DI'd
- Cart/order line grouping

## Ideas

- ULID primary keys
- Inventory
- RMA
- Admin MCP
- Storefront MCP
- Developer MCP

## Done

Nothing to show!
