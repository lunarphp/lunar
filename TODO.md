# TODO List for Lunar v2

## Approach

Each item below should have a written spec in `specs/` (alongside this file in the Lunar package) before implementation begins. One markdown file per item, named `NNNN-short-slug.md`, starting from `specs/0000-template.md`. See `specs/README.md` for the full convention.

## Outstanding

- Region concept to define channel, currency, language, tax_zone, countries and price display
- `StorefrontContext` for CartSessionManager and other services
- Add Vendors concept to support marketplace developments
- Make order line purchasables optional
- Add events, including specific events for cache invalidation
- Stop storing shipping options as polymorphic purchasables on cart/order lines
    - Currently `ShippingOption` is a non-Eloquent value object from `ShippingManifest`, yet lines morph to it via a hardcoded `purchasable_id => 1` (see `CreateShippingLine`)
    - Drop the fake morph: rely on the existing `type = 'shipping'` column and store the option `identifier` + a snapshot of name/price/meta directly on the line
    - Make `purchasable_type`/`purchasable_id` nullable (ties in with "Make order line purchasables optional")
    - Resolve the live `ShippingOption` from `ShippingManifest` by identifier when needed, rather than via the polymorphic relation
- Add `public_id` (ULID) to externally-addressable models
- Cart/order line grouping
- Split out Promotions concept from Discounts
- Add cart totals caching in the database
- Add Boost guidelines to packages
- Order fulfilments & derived order status — line-item `Fulfilment` records (own `pending → shipped → delivered` lifecycle), `payment_status` derived from `transactions` (Shopify `financial_status` set), `fulfilment_status` rolled up from fulfilments, and the headline `Order::$status` derived from payment × fulfilment with `OnHold`/`Cancelled`/`Refunded` manual overrides; reintroduces the `OrderStateConfig` resolver + `OrderStateCategory` deferred from 0021 (spec 0022)


## Ideas

- Inventory
- RMA
- Admin MCP
- Storefront MCP
- Developer MCP
- Filament package additions — see [`packages/filament/IDEAS.md`](packages/filament/IDEAS.md)

## Done

- Add an Upgrade package for those migrating from v1.x (using Rector) — land first so subsequent breaking specs can ship their Rector rules and data migrations into it
- Flatten v1.x migrations into a v2 baseline — single flat set of migration files at v2.0.0; upgrade package handles the v1 → v2 schema transformation and rewrites the `migrations` ledger
- Change to `\Lunar\Core` namespace
- Filament v5 upgrade (spec 0004)
- Filament v5 schemas refactor (spec 0005)
- Extract `lunarphp/filament` bridge package and reshape the install model (spec 0006)
- Pages refactor — inline the 10 page-extension traits into the 5 base page classes (spec 0007)
- Reusable Filament entity-selector components — 16 selector classes in `lunarphp/filament` replacing 17+ duplicated implementations across admin and filament (spec 0008)
- Filament-native verbs and discoverability — first-party actions library (refund, capture, fulfilment status, duplicate product, bulk publish/unpublish/archive, stock adjust) + Filament global search descriptors lifted into the bridge package (spec 0009)
- Publishable admin resources + Staff to core (spec 0010) — `lunar:admin:publish` command + `LunarPanel::excludeResources()` give consumers a real migration path off `lunarphp/admin`; Staff (model, migrations, factory, `Auth\Manifest`, lang, DTOs) moves into `lunarphp/core` so non-Filament panels can share it; `LunarPlugin::make()` wires bridge widgets/global-search/actions onto any Filament v5 panel
- Support `Model::preventLazyLoading()`
- Price data type / cast refactor — replace per-attribute `DataTypes\Price` cast with plain `integer` cast + `FormatsPrices` trait; new `PriceValue` data object for non-Eloquent currency-aware values (spec 0012)
- `Base/` directory reorganisation — every class drained into a semantic home (`Casts/`, `Concerns/`, `Contracts/`, `DataObjects/`, `Enums/`, `FieldTypes/`, `Manifests/`, `Media/`, `Models/`, `Modifiers/`, `Orders/`, `Telemetry/`, `ValueObjects/`); `*Interface` suffix dropped on contracts; `BaseModel` renamed to `Models\Base`; `Base/` namespace deleted (spec 0013)
- `PriceCalculatorInterface` consolidates money arithmetic — half-up `percentage`, strict-inverse `withTax`/`withoutTax`, largest-remainder `distribute`, bc-aware `toMinor`/`toMajor`; routed through every previously inline rounding site; new `PriceCalculator` facade (spec 0014)
- Ensure all service-layer classes are DI'd — actions/managers/drivers/generators constructor-inject collaborators and bind to `Contracts\Actions\…` interfaces in `LunarServiceProvider`; `AbstractAction` and the `lunar.cart.actions.*` / `fingerprint_generator` / `discounts.coupon_validator` config-string swaps removed; `RewriteActionRunCallRector` migrates `Action::run()` callers (spec 0016)
- Change `compare_price` to `list_price`
- Add dedicated `name` / `description` / `short_description` fields — promote Product/Collection name + description out of `attribute_data` into translatable columns and add a translatable `short_description`; Brand gains translatable description/short_description but keeps a plain string name; reads route through `translate()`, search indexes per locale, Filament binds explicit fields; one-way v1→v2 backfill migration + `translateAttribute`→`translate` Rector rule (spec 0018)
- Attribute system redesign — id-keyed raw `attribute_data` JSON on disk + handle-keyed `FieldType` collection in memory; drop the morph columns on `Attribute` / `AttributeGroup` for a nullable group FK + typed `attribute_models` join + renamed `product_type_attribute` pivot; shared `AbstractFieldType` base + `FieldTypeEnum` + relocated `Manifests\FieldTypeManifest`; `AttributeCache` + observer + `PurgeAttributeData` job keep the new shape consistent; one-way v1→v2 data migration + Rector renames in the upgrade package (spec 0019)
- State machines — `spatie/laravel-model-states` v2 across core. Channel (Active/Inactive), Product/Collection (Draft/Published/Archived), and Order as a **single** transition-guarded `OrderState` machine (`AwaitingPayment → InProcess → Shipped → Complete`, plus `OnHold`/`Cancelled`/`Refunded`). `OrderStateConfig` contract is the single seam for adding bespoke states, transitions and notifications. `SoftDeletes` retired from Product/ProductVariant/Channel/Collection; baseline migrations edited in place. PHP minimum bumped to 8.4 due to upstream `spatie/laravel-model-states` PHP/Laravel matrix split. The payment/fulfilment decomposition was deferred to spec 0022 (spec 0021)
