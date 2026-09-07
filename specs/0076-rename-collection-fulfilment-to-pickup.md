# 0076 — Rename the collection fulfilment method to pickup

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-09-07
- TODO item: Rename the collection fulfilment method and states to pickup — Shopify/Woo/Magento-aligned terminology (spec 0076)

## Problem

Lunar calls the in-store handover fulfilment method "collection" and its
terminal state "collected". The rest of the e-commerce ecosystem settled on
"pickup": Shopify fulfilments move through `ready_for_pickup` → `picked_up`
and merchants configure "Local pickup"; WooCommerce ships a "Local pickup"
shipping method; Magento calls it "In-Store Pickup". Staff arriving from any
of those platforms — and integrations mapping Lunar's states onto external
systems — have to translate our vocabulary at every boundary.

The word is also the most overloaded identifier in the codebase. Three
unrelated classes are named `Collection` (`Lunar\Core\Models\Collection`, the
catalogue tree; `Lunar\Core\Drivers\FulfilmentMethods\Collection`; and
`Lunar\Shipping\Drivers\ShippingMethods\Collection`), on top of
`Illuminate\Support\Collection` which nearly every file imports. Call sites
alias their way around it (`use ...\Collection as CollectionMethod` in
`FulfilmentMethodManifest` and the tests; `use Illuminate\Support\Collection
as SupportCollection` inside the driver itself), and grepping for the
fulfilment concept means wading through catalogue and Eloquent noise.

The vocabulary appears in five places:

1. **The fulfilment method key** — `Drivers/FulfilmentMethods/Collection.php`,
   `KEY = 'collection'`, persisted in `fulfilments.method`.
2. **The state names** — `States/Fulfilment/ReadyForCollection.php`
   (`ready-for-collection`) and `Collected.php` (`collected`), persisted in
   `fulfilments.state`.
3. **The routing flag** — `ShippingOption::$collect`, stamped onto the order's
   shipping line as `meta['collect']` by `CreateShippingLine` and read back by
   the driver's `claim()`.
4. **UI strings keyed by the method key** — panel `orders.php`
   (`handed_over_collection`, `fulfil_label_collection`), admin `order.php`
   (`columns.handed_over.collection`, `actions.fulfil.labels.collection`),
   core `fulfilment.php` / `states.php`, each across every shipped locale.
5. **The table-rate-shipping driver** — `Drivers/ShippingMethods/Collection`,
   driver key `'collection'` persisted in `shipping_methods.driver`, offered
   in the Filament driver select, sets `collect: true` on its options.

## Proposal

Rename the concept to "pickup" end to end — classes, persisted keys, the
routing flag, and the merchant-facing labels — matching Shopify's state
vocabulary exactly.

| Current | Proposed |
| --- | --- |
| method key `collection` | `pickup` |
| `Lunar\Core\Drivers\FulfilmentMethods\Collection` | `Lunar\Core\Drivers\FulfilmentMethods\Pickup` |
| state `ready-for-collection` / `ReadyForCollection` | `ready-for-pickup` / `ReadyForPickup` |
| state `collected` / `Collected` | `picked-up` / `PickedUp` |
| `ShippingOption::$collect` / `meta['collect']` | `ShippingOption::$pickup` / `meta['pickup']` |
| `Lunar\Shipping\Drivers\ShippingMethods\Collection`, driver key `collection` | `...\Pickup`, driver key `pickup` |

### Core

- Rename the driver class to `Pickup`, `KEY = 'pickup'`, label from
  `lunar::fulfilment.methods.pickup`. The `orderCollects()` helper becomes
  `orderPicksUp()` and reads `meta['pickup']`. Registration in
  `FulfilmentMethodManifest::registerCoreMethods()` drops its alias.
- Rename the states: `ReadyForPickup` (`$name = 'ready-for-pickup'`, label
  "Ready for pickup") and `PickedUp` (`$name = 'picked-up'`, label
  "Picked up"). Transition tables, `defaultState()`, and `fulfilledState()`
  update mechanically.
- `ShippingOption` constructor property `collect` → `pickup`;
  `CreateShippingLine` stamps `meta['pickup']`.
- `FulfilmentFactory` states `collection()` / `collected()` →
  `pickup()` / `pickedUp()`.
- Docblock mentions in `Fulfilment`, `FulfilFulfilment`,
  `TransitionFulfilment`, `FulfilsFulfilment`, `ResolveFulfilmentStatus`, and
  `FulfilmentStateCategory` follow the new vocabulary.

### Lang keys (every shipped locale)

Key renames in four file families, values retranslated where the term changes:

- core `states.php`: `fulfilment.ready-for-pickup`, `fulfilment.picked-up`.
- core `fulfilment.php`: `methods.pickup`.
- panel `orders.php`: `handed_over_pickup`, `fulfil_label_pickup` (the suffix
  is the method key, looked up dynamically by `OrderShowController`).
- admin `order.php`: `columns.handed_over.pickup`,
  `actions.fulfil.labels.pickup`.

English values change to "Pickup", "Ready for pickup", "Picked up",
"Mark picked up", "Picked up at". Most other locales already translate the
concept as pickup (de `Abholung`, es `Recogida`, fr `Retrait`, ro `Ridicare`)
so only their keys move; each value is checked against the locale's existing
fulfilment terminology while touching the file (e.g. mn's literal
`Цуглуулга` in the table-rate-shipping driver options is a mistranslation —
it should match the `Очиж авах` used by mn `fulfilment.php`).

### Table-rate shipping

- Rename the shipping rate driver class to `Pickup`; `name()` returns
  "Pickup"; it sets `pickup: true` on its options.
- `ShippingManager`: `createCollectionDriver()` → `createPickupDriver()`,
  driver key `'collection'` → `'pickup'`.
- `ShippingMethodForm`'s driver select and the `shippingmethod.php` lang
  files offer `pickup`.
- `shipping_methods.driver` values are persisted, so existing rows are
  rewritten by the upgrade migration below.

### Panel / demo data / tests

- Panel: docblock and comment mentions in `OrderFulfilmentController` and
  `FulfilmentCard.vue`; the `FulfilmentCard.test.ts` fixture method/labels.
  No behavioural frontend change — labels come from the server.
- Admin: docblock mentions in `OrderFulfilments`.
- Demo data: `OrdersGenerator` creates the pickup fulfilment with
  `method => 'pickup'`.
- Tests: `FulfilmentMethodTest` (including the registry-order expectation
  `['digital', 'pickup', 'shipping']`), `FulfilmentTransitionTest`, panel
  `OrderFulfilmentTest`, admin `OrderFulfilmentsTest`, factory state
  call sites.

## Alternatives considered

- **Relabel only (keep persisted keys and class names, change English UI
  strings).** No breaking change, but the API, database values, and state
  names keep diverging from what the UI says, and the three-way `Collection`
  class collision stands. The point of the rename is the vocabulary
  developers and integrations touch, not just the label.
- **`local-pickup` (WooCommerce) or `in-store-pickup` (Magento) as the key.**
  The qualifiers add nothing inside Lunar — the method is defined by the
  customer picking the order up rather than where from — and Shopify's
  unqualified `pickup` / `picked_up` is the closest match to our state names.
- **Rename the fulfilment side only, leave the table-rate-shipping driver.**
  Rejected: the storefront-facing shipping option would still be labelled
  "Collection" and the `collect` flag would feed a `pickup` method. Decided
  to include it despite the extra v1 data migration.
- **Do nothing.** The overload and the ecosystem mismatch only get more
  expensive to fix after v2 ships and third parties build on the state names.

## Migration impact

- **Database migrations**: none to the v2 baseline (the affected columns —
  `fulfilments.method`, `fulfilments.state`, `order_lines.meta` — are
  schema-unchanged; only the values we write change). One new migration in
  the **upgrade** package rewriting `shipping_methods.driver` from
  `'collection'` to `'pickup'` for v1 databases.
- **Existing v2 alpha databases** hold stale values (`method = 'collection'`,
  `state = 'collected'` / `'ready-for-collection'`, `meta.collect`). Per the
  alpha policy no change migration ships — re-seed (demo data regenerates).
  This must land before v2 releases; afterwards it would require a data
  migration.
- **Breaking changes / Rector**:
  - `Lunar\Shipping\Drivers\ShippingMethods\Collection` exists in v1 — update
    its `LunarSetList::V1_TO_V2_CLASS_RENAMES` entry to point at `...\Pickup`.
  - `ShippingOption::$collect` exists in v1 — new Rector rule renaming the
    `collect:` named argument on `ShippingOption` construction and
    `->collect` property access to `pickup`.
  - The core fulfilment method and state classes are v2-new (spec 0031), so
    they need no v1 rules; v2-alpha consumers get the rename in release
    notes.
- **Translation / locale impact**: key renames plus retranslated values in
  core `states.php` + `fulfilment.php` and panel `orders.php` and admin
  `order.php` (16 locales each), and table-rate-shipping `shippingmethod.php`
  (14 shipped locales).
- **Filament / admin impact**: lang keys and docblocks only; the fulfil /
  handed-over labels resolve per method key as before.

## Open questions

- Does any v1 order-creation path persist the shipping option's `collect`
  flag into data the upgrade package carries over? Current understanding is
  no — `meta['collect']` is stamped by v2's `CreateShippingLine`, and the
  upgrade backfill (`2026_06_01_000009`) creates only `shipping`/`shipped`
  fulfilments — but verify against v1 during implementation; if v1 does
  persist it anywhere, the upgrade migration also rewrites that key.

## References

- `packages/core/src/Drivers/FulfilmentMethods/Collection.php`,
  `packages/core/src/States/Fulfilment/{ReadyForCollection,Collected}.php` — the classes being renamed.
- `packages/core/src/Pipelines/Order/Creation/CreateShippingLine.php` — where the routing flag is stamped.
- `packages/table-rate-shipping/src/Drivers/ShippingMethods/Collection.php` — the v1-era shipping driver.
- Shopify fulfilment event statuses `ready_for_pickup` / `picked_up`; WooCommerce "Local pickup"; Magento "In-Store Pickup" — the terminology being aligned to.
- [[0031-fulfilment-methods]] — introduced the method/state seam this renames.
- [[0022-order-fulfilments]] — the fulfilment model itself.

## Implementation plan

- [ ] Slice 1 — The rename across core, panel, admin, demo data, and table-rate-shipping: classes, persisted keys, the `pickup` flag, lang files in every locale, tests and fixtures, docblocks.
- [ ] Slice 2 — Upgrade path: `shipping_methods.driver` data migration, `LunarSetList` entry update, `ShippingOption` flag Rector rule, upgrade-suite tests.
