# TODO List for Lunar v2

## Approach

Each item below should have a written spec in `specs/` (alongside this file in the Lunar package) before implementation begins. One markdown file per item, named `NNNN-short-slug.md`, starting from `specs/0000-template.md`. See `specs/README.md` for the full convention. Item detail lives in the spec — keep entries here to a line.

Items tagged _(judgement)_ are genuine line-calls worth revisiting.

## Outstanding

- Inertia admin panel — new `lunarphp/panel` package: auth, extension points (navigation, slots, table columns, row/bulk/page actions with shared ordering), Customers CRUD, Channels settings (spec 0049)
- Panel order-value chart on the customer edit page + `TimeSeriesChart` on the add-on surface (spec 0050)
- Panel edit drafts — autosaved pending edits with field-level conflict detection (spec 0051)
- Panel Brands section + shared catalog editing surfaces (media, attributes, URL slugs, collection picker) (spec 0052)
- Panel Collections section — group tree, hierarchy, curated products, availability (spec 0055)
- Panel Product Types section — attribute mapping, type-level content, media, tax defaults (spec 0056)
- Panel dashboard — registrable widgets, per-staff layout, brand chart palette (spec 0058)
- Panel media groups — render every registered media collection on catalog edit screens, with a file uploader for non-image groups (spec 0060)
- Default professional customer notifications for the order lifecycle (spec 0036) _(judgement)_
- Bulk order operations — goal-oriented bulk actions on the orders table (spec 0026)
- Line-item refunds — refund specific lines/quantities via a dedicated refund page (spec 0028)
- Order print templates — Print dropdown of selectable PDF templates, ships an Advice Note (spec 0027)
- Cart/order line grouping — grouping key on the `*_lines` tables _(judgement)_
- Cart totals caching in the database — additive performance optimisation
- Long-lived worker safety — scope per-visitor service state (`CartSession`, `StorefrontSession`, `DiscountManager`) behind the new lifetime conventions (spec 0064)
- Add Boost guidelines to packages

## Ideas

- Cache toolkit follow-ons — outbound webhooks, change feed / sync cursor, internal derived caches (ride spec 0043 events)
- Cache-invalidation deferrals — media as a tracked satellite; a `CartLinesUpdated` event (from spec 0043)
- Location-scoped availability & stock routing — per-location stock selection + sell-time routing/splitting (follow-on to 0038)
- Checkout stock reservations — hold stock via the `ReservesStock` seam at checkout start (follow-on to 0038)
- Selling-policy rework — declarative model: deny-oversell, sell-against-incoming, continue-selling boolean (follow-on to 0038)
- Low-stock thresholds & notifications — reorder thresholds firing notifications (follow-on to 0038)
- Returned-goods quarantine — route returns through `unavailable` for inspection; pairs with RMA
- Storefront REST API — first-party `lunar/api` storefront API (see `specs/draft-storefront-api.md`)
- RMA
- Admin MCP
- Storefront MCP
- Developer MCP
- Filament package additions — see [`packages/filament/IDEAS.md`](packages/filament/IDEAS.md)

## Done

- Upgrade package for v1.x migrators, using Rector (spec 0001)
- Flatten v1.x migrations into a v2 baseline (spec 0003)
- `\Lunar\Core` namespace change (spec 0002)
- Filament v5 upgrade (spec 0004)
- Filament v5 schemas refactor (spec 0005)
- Extract `lunarphp/filament` bridge package + reshape the install model (spec 0006)
- Pages refactor — inline page-extension traits into the base page classes (spec 0007)
- Reusable Filament entity-selector components (spec 0008)
- Filament-native verbs + global-search discoverability (spec 0009)
- Publishable admin resources + Staff to core (spec 0010)
- Support `Model::preventLazyLoading()` (spec 0011)
- Price data type / cast refactor + `PriceValue` (specs 0012, 0015)
- `Base/` directory reorganisation (spec 0013)
- `PriceCalculator` money arithmetic (spec 0014)
- Service-layer dependency injection (spec 0016)
- Rename `compare_price` to `list_price` (spec 0017)
- Dedicated `name` / `description` / `short_description` fields (spec 0018)
- Attribute system redesign (spec 0019)
- State machines + retire soft-deletes (spec 0021)
- Automatic notifications onto manifests (spec 0037, superseded by 0035)
- Order fulfilments, derived statuses & open/closed lifecycle (specs 0022, 0024, 0025; demo data 0023)
- Entry-point conventions & fulfillable order lines (specs 0029, 0030)
- Fulfilment methods (spec 0031)
- Wire & gate fulfilment notifications (spec 0034)
- Interactive "Notify customer" order action (spec 0035)
- Auto-close settled orders (spec 0032)
- Multi-tenant homes for new config (spec 0033)
- Inventory fundamentals (spec 0038)
- `StorefrontContext` for services (spec 0040)
- Region concept (spec 0039)
- Retire model class substitution (spec 0041)
- Model query builders — registerable local scopes (spec 0042)
- Cache invalidation & event coverage (spec 0043)
- Storefront cache tagging & dependency resolution (spec 0044)
- Optional order-line purchasables & shipping-option de-morph (spec 0045)
- `public_id` (ULID) external addressing (spec 0046)
- Rename `product_variants.purchasable` to `selling_policy` (spec 0048)
- Panel Products section — list KPIs, product editing with options/variant builder, variant editing, catalog nav restructure (spec 0057)
- Per-attribute validation rules across both panels (spec 0062)
- Standalone attributes surface in the Filament admin (spec 0063)
