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
- Ship default professional customer notifications for the order lifecycle
    - The system already *expects* notifications (e.g. order cancellation dispatches `OrderCancelled` with a "notify the customer" toggle, and `lunar.orders.notifications` keys off `cancelled` / `paid` / `fulfilled` / per-parcel `shipped`), but core ships **none** — every key is an empty/commented example, so out of the box a customer is never emailed and a developer must hand-roll each notification + template
    - Provide a set of sensible, branded-but-overridable default `Notification` classes + mail templates for the key events: order confirmation (placed), payment received, fulfilment shipped (with tracking), order cancelled, refund issued
    - Wire them as the defaults under `lunar.orders.notifications` (and the per-parcel fulfilment config) so the admin toggles actually send something; keep them publishable/swappable and respect the existing notify flags
    - Consider a shared mailable layout + a way to disable per-event; ties into the storefront/branding work
    - (spec 0036 draft) — spec 0035 ships a stop-gap general-purpose `OrderUpdate` default in the `CustomerNotifications` catalogue; this item covers the branded lifecycle set
- Move automatic lifecycle notifications off `config('lunar.orders.notifications')` onto a `LifecycleNotifications` manifest — the last class-registry still living in config, inconsistent with the container convention (spec 0033) and with the `CustomerNotifications` manifest (spec 0035). Mirror the manifest pattern, keep it separate from the manual catalogue, retire the config key with a boot-time bridge for upgrades. (spec 0037 draft)
- Wire and gate fulfilment notifications — the per-parcel path is stubbed but never connected (`FulfilmentStatusUpdated` has no listener, `FulfilmentStateConfig::notificationsFor()` no caller), so a configured `shipped`-with-tracking notification never fires; and automatic fulfilment sends can't be suppressed per operation the way cancellation can. Add a `SendFulfilmentStatusNotifications` listener resolving through the method-aware seam (instantiated with the `Fulfilment` so it carries tracking), thread a `notify` flag onto the fulfilment verbs + events so a ship/return can be done quietly, and surface a conditional "Notify customer" toggle in the fulfilment modals (shown only when a notification is configured for the target state, so it can never be a no-op). (spec 0034 proposed)
- Interactive "Notify customer" order action — restore v1's lost ad-hoc customer email: an order-level action to pick a notification variant, attach an optional custom message, choose recipients (billing/shipping contact + ad-hoc), and record the send on the order timeline. Variants come from a new `CustomerNotifications` registry (manifest pattern, mirroring `CancelReasons`/`HoldReasons`); the send + audit live in a core `NotifyCustomer` action so API callers log too; re-homes the orphaned `email-notification` activity renderer. Order-level, not fulfilment-level. (spec 0035 proposed)
- Add `public_id` (ULID) to externally-addressable models
- Cart/order line grouping
- Split out Promotions concept from Discounts
- Add cart totals caching in the database
- Add Boost guidelines to packages
- Order fulfilments & order lifecycle — line-item `Fulfilment` records with a guarded per-parcel lifecycle (`pending → in-progress → shipped`, plus `cancelled`/`returned`, reversible un-ship/undo-return, and on-hold); split-down model with Locations; `payment_status` + `fulfilment_status` derived rollups on the order; the hand-driven headline `Order::$status` is **removed** in favour of an open/closed (`closed_at`) archive; shipping-carrier registry + one-to-many tracking; order cancellation (`cancelled_at`). Reverses the derived-headline design 0021 deferred. (specs 0022, 0024, 0025; demo-data 0023 drafted)
- Bulk order operations — Shopify-style bulk actions on the orders table that are **goal-oriented operations, not blind transitions**: "Mark as shipped", "Mark as fulfilled", "Close", "Cancel". Each iterates the selection, filters targets by the action's existing `canRun()` predicate (skipping parcels/orders that can't move — already shipped, cancelled, on hold), executes per parcel/order, and reports a "X of Y" tally. Ships every fulfillable parcel of multi-parcel orders, with optional/blank tracking backfilled later. (spec 0026 drafted)
- Line-item refunds — replace the amount-only refund (`RefundOrder::execute(order, transaction, amount, notes)`) with **refunding order lines directly**: select lines + quantities (plus shipping and an optional adjustment), so the system records **which lines/quantities have been refunded**. New `refund_lines` linkage off the refund transaction + denormalised `refunded_quantity` on `order_lines`; a Shopify-style **dedicated refund page** (not a modal) that lists refundable lines, computes the suggested total, captures a reason/note/notify, and dispatches to the payment driver. Payment-status rollup (`ResolvePaymentStatus`) stays money-derived. Restock deferred to inventory. (spec 0028 drafted)
- Order print templates — replace the single "Download PDF" button with a **"Print" dropdown** offering selectable PDF templates. Core ships **one** default template, an **Advice Note** (packing/delivery slip — items + quantities + addresses), as a publishable blade view; the existing priced "invoice" view is **retired** (Lunar doesn't model invoices). Developers register additional templates (label + view + filename) via config; the existing template-agnostic PDF pipeline (`DownloadPdfController`, `DownloadPdfAction`) renders each. Format stays PDF. (spec 0027 drafted)
- Fulfilment methods — make the fulfilment *flow* a registered seam instead of hardwiring `Fulfilment` as a shipment. A `FulfilmentMethod` driver (registry + `FulfilmentMethods` facade, mirroring the carrier manifest) owns a parcel's state graph, which order lines it claims, and whether it carries tracking; `Fulfilment` stores its `method` as a key. Core ships **three** methods on the seam — `shipping` (0022's behaviour refactored on), `collection` (click-and-collect: `ready-for-collection → collected`, no tracking, consumes the existing `ShippingOption::collect`), `digital` (manual provisioning: `pending → provisioned`) — so a consumer registers a bespoke flow (e.g. pharmacy prescription verification) the same way. Per-parcel states stay pluggable but each declares a fixed `FulfilmentStateCategory` (`outstanding/fulfilled/returned/cancelled`) so `ResolveFulfilmentStatus` stays method-agnostic; new `order_lines.requires_fulfilment` (from `Purchasable::requiresFulfilment()`) re-keys `fulfillableLines()` off physical-only, fixing digital-only orders resolving instantly to `Fulfilled`. (spec 0031 accepted)
- Auto-close settled orders — an opt-in `lunar.orders.auto_close` preference (off by default) that closes an order the moment it becomes fully **paid and fulfilled**, reusing `CloseOrder` so a settled order drops out of the Open work queue without a manual click (Shopify's "automatically archive" setting). A `CloseSettledOrder` listener on the existing `OrderPaymentStatusUpdated` / `OrderFulfilmentStatusUpdated` events; close-only (a later return/refund never reopens); no schema, no breaking change. (spec 0032 accepted)
- Multi-tenant homes for this branch's new config — the config this branch adds bakes swap seams and per-store data into global config, which can't vary per store without runtime mutation; relocate before shipping. Swap seams → container: `fulfilment.methods` and `shipping.carriers` now register as classes (`GenericFulfilmentMethod`/`GenericCarrier` removed; core carriers registered via `CarrierManifest`). Per-store data → code default + override seam (interim, ahead of the store-scoped Channel end state): `fulfilment.hold_reasons`/`orders.cancel_reasons` via the `HoldReasons`/`CancelReasons` manifests, `orders.auto_close` via `OrderSettings`. Scoped to this branch's new config only; a full review of the pre-existing config surface + the convention is a separate follow-up. (spec 0033 accepted)


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
