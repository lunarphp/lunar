# 0072 — Panel Discounts section

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Panel Discounts section — list, discount editing, targeting, availability and usage limits (spec 0072)
- Depends on: [[0073-split-amount-off-discount-type]] (shipped)

## Problem

Discounts is the last section missing from the Inertia panel. With Orders landing in
[[0066-panel-orders-section]] and Customers shipped with [[0049-inertia-panel]], the
`SalesSection` covers everything except discounts, and there is no discounts surface in
`lunarphp/panel` at all — staff must fall back to the Filament admin's `DiscountResource`
to create or edit one.

### What core actually models

A `Discount` (`lunar_discounts`) is a flat, top-level record — there is no campaign or
promotion wrapper (see the rejected [[0047]] promotions layer, marked `declined` in
`specs/README.md`). Its columns are `name`, `handle` (unique), `coupon` (unique, cast
through `CouponString`), `type` (the discount-type class name), `starts_at` / `ends_at`,
`uses` / `max_uses` / `max_uses_per_user`, `priority`, `stop`, `restriction`, and a
`data` jsonb blob owned by the type.

- **Derived status** — `Discount::getStatusAttribute()` collapses the date window into
  `active` / `pending` / `expired` / `scheduled`. It is an accessor, not a state machine.
- **Discount types** are registered on `DiscountManager::$types` and extended via
  `Discounts::addType()`. Three exist in the monorepo today:
  - `AmountOff` (core) — one class covering two behaviours behind a `data.fixed_value`
    flag. [[0073-split-amount-off-discount-type]] replaces it with `PercentageOff`
    (`data.percentage`) and `FixedAmountOff` (`data.amounts`) and lands first; this section
    is written against that pair.
  - `BuyXGetY` (core) — `data.min_qty`, `data.reward_qty`, `data.max_reward_qty`,
    `data.automatically_add_rewards`.
  - `ShippingDiscount` (`table-rate-shipping`) — discounts the cart's
    `shippingBreakdown` items per shipping method. Its `data.methods` is a *list* of rules
    (`shipping_method_id` or catch-all, `type` fixed/percentage, `percentage`, `prices` per
    currency), and it never touches cart lines.
- **Conditions** are fixed, not composable: the coupon code, `data.min_prices` (minimum
  spend in minor units per currency code), `max_uses`, `max_uses_per_user`, and the
  `customer_discount` pivot. `AbstractDiscountType::checkDiscountConditions()` is the whole
  set.
- **Availability** rides the shared concerns — `HasChannels` (`channelables` morph pivot:
  `enabled` / `starts_at` / `ends_at`) and `HasCustomerGroups` (`customer_group_discount`:
  `enabled` / `visible` / `starts_at` / `ends_at`).
- **Targeting is split across three tables**, and which table a given entity lands in
  depends on the bucket:

  | Bucket | Products / variants | Collections | Brands | Customers |
  | --- | --- | --- | --- | --- |
  | `limitation` | `discountables` | `collection_discount` | `brand_discount` | `customer_discount` |
  | `exclusion` | `discountables` | `collection_discount` | `brand_discount` | — |
  | `condition` (BuyXGetY) | `discountables` | `discountables` | — | — |
  | `reward` (BuyXGetY) | `discountables` | `discountables` | — | — |

  Collections are the trap: the line-targeting types read them from the
  `collection_discount` pivot, while `BuyXGetY::apply()` reads them from `discountables`
  with a `Collection` morph. The same conceptual "this collection" is stored in two
  different tables depending on the bucket.

- **No core actions** — `packages/core/src/Actions/` has no `Discounts/` directory. Every
  other panel section commits through a core action contract (`UpdatesCollection`,
  `UpdatesBrand`, …); discounts have nothing to delegate to.
- **The only type-form seam is Filament's.** `Lunar\Filament\Contracts\DiscountFormType`
  (re-exported as `Lunar\Admin\Base\LunarPanelDiscountInterface`) lets a type contribute
  `lunarPanelSchema()` / `lunarPanelOnFill()` / `lunarPanelOnSave()`. `ShippingDiscount`
  implements it, which is why `table-rate-shipping` hard-requires `lunarphp/admin` and
  `filament/filament`. A panel equivalent must not repeat that coupling — the panel depends
  only on `core`.
- `restriction` is dead — the column exists in the baseline migration and is referenced
  nowhere in `packages/*/src`.

### The Filament baseline

`Lunar\Admin\...\DiscountResource` is the feature-parity target: a searchable table
(status badge, name, type, starts/ends, coupon), a `DiscountForm` with the main fields plus
per-type sections gated on the selected `type`, a `ManageDiscountAvailability` page
(channels + customer groups) and a `ManageDiscountLimitations` page fronting eight relation
managers (collection/brand/product/variant/customer limitations, product rewards, product
and collection conditions).

### Where the prototype diverges

The `lunar-v2-ui` prototype (`DiscountsList.vue`, `DiscountEdit.vue`, `DiscountForm.vue`,
`DiscountTypeForms/*`, `ConditionForms/*`, `data/discounts.js`) is a good source of layout
and interaction ideas but models a different domain:

- **Every discount belongs to a promotion** (`promotionId`, 1:N) which owns the schedule,
  audience and usage caps. Core has no promotion — that layer was specced and declined.
- **Four flat discount types** — `percentage-off`, `fixed-amount-off`, `free-shipping`,
  `buy-x-get-y`. Core has the first two (after [[0073-split-amount-off-discount-type]]) plus
  `BuyXGetY`; shipping discounting exists only if the table-rate-shipping package is
  installed.
- **A composable `conditions[]` repeater** with five condition types (minimum spend,
  minimum quantity, contains products / collection products / brand products). Core's
  conditions are a fixed set of columns; only minimum spend has a counterpart.
- **Flat `targets[]` / `exclusions[]` arrays** of `{ kind, id }`. Core spreads the same
  information across `discountables`, `collection_discount`, `brand_discount` and
  `customer_discount`, with a `type` discriminator on each.

## Proposal

Ship a Discounts section that presents **core's discount model**, borrowing the prototype's
information architecture — a KPI-led list, a single scrolling edit page with a type-switched
config block and chip-list targeting — but not its promotion layer or composable conditions.

The section is driven by the type registry rather than a hardcoded `if type === …` ladder,
which is what lets a core free-shipping type (and `ShippingDiscount` today) slot in without
touching it. That needs an honest registry, so
[[0073-split-amount-off-discount-type]] — splitting `AmountOff` into `PercentageOff` and
`FixedAmountOff`, with the Filament and upgrade work that forces — lands first as its own
PR. Everything below assumes it has.

The one piece of core work this spec still owns is the missing discount actions.

### Core — discount actions

`Actions/Discounts/` with matching contracts under `Contracts/Actions/Discounts/`,
registered in `ActionServiceProvider::$actions`, mirroring the Brands trio:

- `CreatesDiscount::execute(array $attributes): Discount`
- `UpdatesDiscount::execute(Discount $discount, array $attributes, ?array $channels = null, ?array $customerGroups = null, ?array $targets = null): Discount`
- `DeletesDiscount::execute(Discount $discount): void`

`$targets` is the one addition over `UpdatesCollection`'s signature: an id-keyed map per
bucket that the action fans out to the correct table, so callers never have to know that
collections live in two places.

```php
/**
 * @param ?array{
 *   limitation: array{products: int[], variants: int[], collections: int[], brands: int[], customers: int[]},
 *   exclusion:  array{products: int[], variants: int[], collections: int[], brands: int[]},
 *   condition:  array{products: int[], variants: int[], collections: int[]},
 *   reward:     array{products: int[], variants: int[], collections: int[]},
 * } $targets  null leaves targeting untouched; a present bucket replaces that bucket wholesale
 */
```

The action is the only place that knows the routing table above. It writes in a
transaction, syncing `discountables` per morph type and the three pivots, and it is the
seam a consumer swaps to change targeting semantics. A bucket given a kind it cannot
target — brands on a `condition`, say — raises rather than writing rows no discount type
reads. `customer_discount` has no `type` column, so eligible customers hang off the
`limitation` bucket alone.

`Discount` also gains `scopeScheduled()`, `scopeExpired()` and `scopePending()` alongside
the existing `scopeActive()`, so the derived status is filterable in SQL. The Filament table
can adopt them later.

No schema changes. `restriction` stays as-is — removing it is a separate cleanup, noted in
Open questions.

### Panel — the discount-type seam

The section renders **whatever is in the registry**, never a fixed list. A new panel-side
contract describes how a type is edited:

```php
namespace Lunar\Panel\Contracts;

interface DiscountTypeForm
{
    /** Vue component id, resolved through the panel's component registry. */
    public function component(): string;

    /** Target buckets this type reads — drives which "Applies to" blocks render. */
    public function targetBuckets(): array;

    /** Decode stored `data` for editing (minor units to decimals, and so on). */
    public function toForm(array $data): array;

    /** Encode the edited payload back to stored `data`. */
    public function toStorage(array $data): array;

    /** Validation rules for the type's `data.*` payload. */
    public function rules(): array;
}
```

`toForm()` / `toStorage()` are the renderer-agnostic counterparts of Filament's
`lunarPanelOnFill()` / `lunarPanelOnSave()`; `component()` replaces `lunarPanelSchema()`.

Implementations are **separate classes**, mapped by the owning section through
`Section::discountTypeForms(): array<class-string<DiscountType>, class-string<DiscountTypeForm>>`
— the same extension shape as `tableExtensions()`. Core discount types therefore stay free
of any panel dependency, and a third-party package registers its own type's form from its
own `Section` without depending on `admin` or Filament, which is exactly the coupling
`ShippingDiscount` suffers from today.

`DiscountTypeSchema` (`Panel/Support/`) resolves `Discounts::getTypes()` against that map
into props: `['class' => …, 'label' => …, 'component' => …, 'buckets' => [...], 'data' => [...]]`.
A type with no registered form falls back to `RawDataForm` — a JSON editor over `data` with
all buckets shown — so an unknown type stays editable instead of disappearing.

Three constraints this seam has to hold, taken from the types that exist rather than
invented:

- **`data` is not a flat scalar map.** `ShippingDiscount.data.methods` is a list of rule
  objects. The schema passes `data` through as an opaque array; only the component knows its
  shape.
- **Not every type targets cart lines.** `ShippingDiscount` returns no target buckets at
  all. `targetBuckets()` drives the "Applies to" section, so a cart-level or shipping-level
  type renders none of it.
- **Money scaling is the type's job.** `toForm()` / `toStorage()` scale through
  `PriceCalculator::toMinor()` and `Currency::decimal_places`, never a hardcoded factor —
  which is what lets a type with per-currency prices nested inside a repeater (again,
  `ShippingDiscount`) get it right.

A core free-shipping type (its own follow-up spec, see Open questions) is then a new class plus a
`DiscountTypeForm` and a Vue component, with no change to this section.

### Panel server side

**`SalesSection`** gains a Discounts navigation item (`percent` icon, after Customers) gated
on `sales:manage-discounts` — the handle the Filament `DiscountResource` and
`Lunar\Core\Auth\Manifest` already carry; no new permission. Registers `discounts.index` in
`tableExtensions()`, `DiscountDraftResource` in `draftables()`, and the first-party entries
in `discountTypeForms()`.

**Routes** — prefix `discounts`, names `panel.discounts.*`, middleware
`can:sales:manage-discounts`, following the Customers shape:

- `index` (`GET /`), `create` (`GET /create`), `store` (`POST /`),
  `edit` (`GET /{discount}/edit`), `update` (`PUT /{discount}`),
  `destroy` (`DELETE /{discount}`).
- Draft endpoints: `draft.update` / `draft.destroy` / `draft.commit`, as Customers and
  Collections have.
- `targets.search` (`GET /{discount}/targets/search`) — a single typeahead behind the target
  picker, returning products, variants, collections, brands and customers as
  `{ kind, id, label, hint }` rows, filtered by a `kinds` query param.

**Controllers** (`Http/Controllers/Discounts/`):

- `DiscountIndexController` (uses `ResolvesTableExtensions`) — search over name / handle /
  coupon; filters for status (via the new scopes), type, channel, customer group, and
  has-coupon / automatic; sort by name, priority, starts_at, ends_at, uses. Rows are plain
  arrays: name, handle, status key + label, type label, effect summary, coupon, window,
  priority, `uses` / `max_uses`, and an `edit` URL. A cached KPI strip (active now,
  scheduled, expiring in 7 days, redeemed in 30 days) doubles as filter shortcuts, plus
  `tableProps()`.
- `DiscountCreateController` — `create` renders name, handle, type and start date; `store`
  delegates to `CreatesDiscount` and redirects to `edit`. Everything else is configured on
  the edit page (the Collections precedent).
- `DiscountEditController` — `edit` hand-shapes props (record, type schema, availability
  rows, target chips resolved to labels, currencies, usage summary, activity via
  `Inertia::defer(...)`); `update` and `destroy` delegate to the actions.
- `DiscountTargetSearchController` — the typeahead.

**`DiscountDraftResource`** (`Sections/Sales/`) — the edit page is form-heavy and
single-owner, so it uses the standard draft machinery. Fields: the scalar columns, the
type's `data` payload as one field (its shape is the type's business, so it drafts and
conflict-checks as a unit), `...$this->availabilitySchema->fields()` (reused as-is —
`AvailabilitySchema` already works off any model exposing `channels()` / `customerGroups()`,
which `Discount` does), and one field per target bucket. `commit()` splits availability and
targets back out, runs `toStorage()`, and calls `UpdatesDiscount`.

**Requests** (`Http/Requests/Discounts/`): `DiscountRequest` (name, handle unique, coupon
unique + nullable, type in the registry, dates with `ends_at` after `starts_at`, priority
1–100, `max_uses` / `max_uses_per_user` nullable integers, merging the selected type's
`rules()`), `DiscountTargetsRequest`.

**`DiscountsTableExtension`** (`Sections/Sales/Tables/`) — `EditDiscountAction` and
`DeleteDiscountAction` row actions, plus `DeleteDiscountsAction` and
`SetDiscountsEndedAction` (sets `ends_at` to now, the safe bulk "switch these off") toolbar
actions.

### Frontend

`pages/discounts/{Index,Create,Edit}.vue`, standard scaffold (`PanelLayout` +
`PageHeader` + `PageZone` before/after, enforced by `PageScaffoldTest`).

- **Index** — `DataTable`, `Pagination`, `FilterDropdown`, `KpiCard`, `StatusBadge`,
  `BulkActionsToolbar`. Columns: Status (badge), Name (+ handle beneath), Type (label plus
  effect summary — "15% off", "GBP 20.00 off", "Buy 2 get 1"), Coupon (mono, or an
  "Automatic" chip), Window (starts → ends), Usage (`uses` / `max_uses` with a meter),
  Priority.
- **Create** — name, handle (auto-slugged), type (a card list built from the registry, each
  with its description), start date. Submits and lands on Edit.
- **Edit** — two-column, `useEditDraft` + `DraftActions` + `DraftConflictDialog`:
  - Main: **Details** (name, handle, priority, `stop` toggle with its "stop processing
    further discounts" helper), **Configuration** (the type's component), **Conditions**
    (coupon, minimum spend per enabled currency, `max_uses`, `max_uses_per_user`),
    **Applies to** (one block per bucket the type declares, `TargetChipList` +
    `TargetPickerDialog`), **Activity** (`ActivityTimeline`, deferred).
  - Sidebar `SideCard`s: **Schedule** (`starts_at` / `ends_at` via `DatePicker`, with the
    derived status badge), **Availability** (`AvailabilityCard`, reused verbatim, no
    `withPurchasable`), **Eligible customers** (the `customer_discount` pivot, which has no
    `type` column and so is an audience restriction rather than a product target),
    **Usage** (`uses` against `max_uses`, per-user cap, redemption count).

New components: `DiscountTypeCard.vue`, `TargetChipList.vue`, `TargetPickerDialog.vue`
(built on the existing `ProductPickerDialog` / `CollectionPicker` patterns, extended to the
five target kinds), `DiscountTypeForms/{PercentageOffForm,FixedAmountOffForm,BuyXGetYForm,RawDataForm}.vue`,
`UsageMeter.vue`. `TargetChipList`, `TargetPickerDialog`, `UsageMeter` and the currency-input
helper are exported from `ui.ts` so third-party discount-type forms can reuse them; the
first-party type forms themselves are not exported.

A `percent` icon is added to `Icon.vue` for the navigation item.

### Proving the seam: `ShippingDiscount`

`table-rate-shipping` ships a `ShippingDiscountForm` and registers it from its own section,
giving the panel parity with the Filament form and proving the seam against a real
out-of-core type: repeater-shaped `data.methods`, per-currency prices nested inside each
rule, and no target buckets. Its `component()` is a `ShippingDiscountForm.vue` bundled by
the package through `@lunarphp/panel-vite-plugin`.

This is the one slice that reaches outside `core` and `panel`. It can be deferred to the
free-shipping spec if we would rather keep this one panel-shaped — the cost of deferring is
that table-rate-shipping users see the `RawDataForm` fallback until it lands.

### Translations

New panel `discounts.php` lang group (list, form, target-picker, dialog and flash strings),
English first, then translated into the other 15 locales. `nav.php` gains `discounts`.

Type names come from `DiscountType::getName()`. The core types get their `lunar::` keys in
[[0073-split-amount-off-discount-type]]; `ShippingDiscount` still returns
`lunarpanel.shipping::…` keys for a group whose panel is gone, so it renders as a raw key
string until the seam-proof slice repoints it. `table-rate-shipping` also carries only 14
locale directories (no `de`, `nl`); if that slice lands here, those two are added to bring
it to 16.

### Testing

- **Pest (`tests/core/`)**: the three new actions, including `$targets` fan-out per bucket
  (a collection in `limitation` lands in `collection_discount`; a collection in `condition`
  lands in `discountables`), null-bucket no-ops, and transaction rollback; plus the three
  new `Discount` status scopes.
- **Pest (`tests/panel/Feature/Discounts/`)**: index (rows, each filter, search, KPI shape,
  permission gating), create/store, edit props (type schema, availability rows, resolved
  target chips, deferred activity), update through the draft commit, destroy, target search
  (per-kind filtering, permission gating), bulk actions, money scaling round-trips across a
  two-decimal and a zero-decimal currency, and a registered fake type proving the seam
  (custom component, declared buckets) plus an unregistered one falling back to
  `RawDataForm`.
- **Vitest**: the four type forms, `TargetChipList` add/remove, `TargetPickerDialog` kind
  filtering, `UsageMeter`, status-badge tone mapping.
- `PageScaffoldTest` covers the three new pages. Pint + PHPStan as required.

## Alternatives considered

- **Building the section against the current `AmountOff`** and adapting after the split:
  rejected. The type picker and type filter are among the first things built, and both need
  the two behaviours to be separate registry entries — writing the special-casing and then
  unwinding it costs more than sequencing the specs.
- **Adding a core free-shipping type in this spec**: rejected as scope. Shipping discounting
  already exists as `ShippingDiscount` in `table-rate-shipping`, so the immediate question
  is whether that behaviour belongs in core and how it relates to shipping options — its own
  spec. This section is designed so the answer changes nothing here.
- **A composable conditions repeater** matching the prototype: rejected. Core's conditions
  are fixed columns read by `checkDiscountConditions()`; a repeater UI would imply an
  extensibility that does not exist. Composable conditions are a core change first.
- **Reintroducing a promotion layer** to group discounts as the prototype does: rejected —
  [[0047]] was declined, and nothing has changed. Scheduling and audience live on the
  discount.
- **Reusing Filament's `DiscountFormType` for the panel** rather than a new contract:
  rejected. It returns Filament schema objects, so adopting it would make the panel depend
  on Filament — the coupling that currently forces `table-rate-shipping` to require
  `lunarphp/admin`. A type can implement both contracts and serve both panels.
- **Mirroring Filament's four-page layout** (edit / availability / limitations, plus a
  BuyXGetY variant): rejected. The panel's other sections put everything on one edit page
  with sidebar cards, and the draft machinery assumes a single form. Sub-pages would also
  fragment the target buckets that already confuse people.
- **Normalising the three targeting tables into `discountables` first**: tempting, and the
  split is a genuine wart, but it is a migration plus a rewrite of every type's
  `getEligibleLines()` — separate work. `UpdatesDiscount` hides the split behind one
  signature, which is what the panel needs today and the natural seam for a later
  normalisation.
- **No edit drafts** (immediate saves, as Orders uses): rejected. A discount is a free-text
  form like a collection, not a ledger-driven record; autosave and conflict detection apply.

## Migration impact

- **Database**: no schema changes, no migrations. (The `AmountOff` split's data migration
  belongs to [[0073-split-amount-off-discount-type]].)
- **Breaking changes**: none. Everything here is additive; the breaking work is in 0073.
- **Core public surface**: three new action contracts and implementations plus their
  `ActionServiceProvider::$actions` entries, and three new `Discount` scopes. Additive.
- **Behaviour fix**: `DiscountObserver::deleting()` also detaches `channels()`, which it
  missed while its `Product` and `Collection` siblings did not — deleting a discount was
  orphaning its `channelables` rows on every delete path.
- **Panel public surface**: `Lunar\Panel\Contracts\DiscountTypeForm` (six methods:
  `component`, `targetBuckets`, `toForm`, `toStorage`, `rules`, `summary`),
  `Section::discountTypeForms()`, and four new `ui.ts` exports. `discountTypeForms()`
  is added to `ProvidesNavigation` and `SectionExtension` alongside `Section`, matching
  every other extension hook — a consumer implementing the interface directly rather
  than extending the abstract has to add the method.
- **Permission**: reuses `sales:manage-discounts`; no manifest change.
- **Translations**: new panel `discounts.php` group and a `nav.php` key across all 16
  locales; if the `ShippingDiscount` slice lands here, its lang group is repointed and
  `de` / `nl` are added to `table-rate-shipping` to bring it to 16.
- **Filament / admin**: untouched by this spec. The `DiscountResource` keeps working
  alongside the panel; its `AmountOff`-driven changes belong to 0073. The resources are
  deliberately not moved onto the new discount action contracts — no Filament resource uses
  a core action today, and making them do so is a pass across every resource, not this one.
- **No new npm dependencies.**

## Decisions taken during implementation

- **Discount handles carry no format rule.** The Filament admin only runs the name
  through `Str::snake`, which leaves punctuation intact, so handles like `sofia_o'kon`
  already exist. A pattern in the panel would make them uneditable. The panel's create
  screen generates a clean snake_case handle, matching Filament, but accepts anything
  Filament would.
- **The channel and customer-group filters read the pivot's `enabled` flag**, not the
  row's existence: `HasChannels` and `HasCustomerGroups` attach every channel and group
  when a discount is created, so a filter on attachment alone matches everything.
- **The redemptions KPI is a lifetime total, not a 30-day window** as first written.
  `uses` is a bare counter with no per-redemption timestamp, and the only timestamped
  table (`discount_user`) records signed-in redemptions only — `markAsUsed()` skips the
  attach for a guest cart — so a windowed figure would silently omit guest checkouts.
- **The type-effect summary in the list ("15% off") arrived with slice 4**, as a
  `summary()` method on `DiscountTypeForm`. Deriving it earlier would have needed the
  hardcoded type ladder this section exists to avoid.
- **`data` has two owners, so `DiscountDataSchema` composes it.** `min_prices` is read by
  `AbstractDiscountType::checkDiscountConditions()` for every type, but it lives in the
  `data` column a type form owns — a form returning only its own keys from `toStorage()`,
  which is the natural way to write one, would drop the minimum-spend condition. Every
  read and write of `data` goes through the composing schema instead of a type form
  directly.
- **The type's `data.*` rules only apply when the request carries `data`.** Otherwise a
  full update that omits the payload would be rejected for a missing `data.percentage`;
  omitting it now leaves the stored payload untouched, matching how `UpdatesDiscount`
  treats availability and targeting.
- **The target picker is one search across the bucket's kinds**, with kind chips on the
  rows, rather than a tab per kind — the open question in this spec, resolved in favour of
  the prototype's shape. It also excludes what the bucket already holds, which is what
  earns the `{discount}` in its route.
- **`UpdateDiscount::BUCKET_KINDS` is public.** It describes core's targeting schema
  rather than the action's behaviour, and the panel needs the same map to know which
  blocks a bucket can show. Restating it in the panel would let the picker drift from
  what the action accepts.
- **`DiscountDraftResource::rules()` filters the request rules to draftable fields.** A
  commit only ever carries draftable values, so the endpoint's `type` rule — a column
  fixed once the discount exists — would reject every commit.

## Open questions

- **Free shipping** — agreed as a follow-up spec, and it now also owns slice 6's
  `ShippingDiscountForm`. The open part is its shape: promote
  `ShippingDiscount` from `table-rate-shipping` into core and generalise it away from
  `ShippingMethod`, or add a narrower core `FreeShipping` type and leave `ShippingDiscount`
  where it is? Resolve against the shipping-options work before writing it. Either answer
  slots into this section unchanged.
- **`discounts.restriction`** — dead column with no reader anywhere; tracked as an open
  question on [[0073-split-amount-off-discount-type]], which is already in that table.

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` —
  `src/pages/{DiscountsList,DiscountEdit}.vue`,
  `src/components/{DiscountForm,DiscountFormSheet,DiscountBreakdown,TargetChipList,TargetPickerDialog}.vue`,
  `src/components/DiscountTypeForms/*`, `src/components/ConditionForms/*`,
  `src/data/discounts.js`. Inspiration only — its promotion layer and composable conditions
  do not match core.
- Feature-parity baseline: `admin` `DiscountResource` (+ its four pages) and `filament`
  `Schemas/Discount/DiscountForm`, `Tables/Discount/DiscountTable`,
  `RelationManagers/Discount/*`.
- Core: `Models/Discount`, `Models/Discountable`, `DiscountTypes/AbstractDiscountType`,
  `Managers/DiscountManager`, `Pipelines/Cart/ApplyDiscounts`.
- Third-party type reference: `table-rate-shipping` `DiscountTypes/ShippingDiscount` and
  `tests/shipping/Unit/DiscountTypes/ShippingDiscountTest`.
- [[0049-inertia-panel]] — panel architecture and extension model.
- [[0055-panel-collections-section]] — the availability + draft-resource pattern this reuses.
- [[0057-panel-products-section]] — the entity-picker pattern the target picker builds on.
- [[0066-panel-orders-section]] — the sibling Sales section; this completes the set.
- [[0051-panel-edit-drafts]] — the autosave/conflict machinery.
- [[0073-split-amount-off-discount-type]] — the prerequisite core split, shipping first.
- Spec 0047 (Promotions: a campaign layer over discounts) — declined; see `specs/README.md`.

## Implementation plan

Prerequisite: [[0073-split-amount-off-discount-type]] merges first.

- [x] Slice 1 — Core actions: `Actions/Discounts/{CreateDiscount,UpdateDiscount,DeleteDiscount}`
      + contracts + `ActionServiceProvider` entries, with the `$targets` fan-out across
      `discountables` / `collection_discount` / `brand_discount` / `customer_discount`;
      `Discount` status scopes; tests.
- [x] Slice 2 — Panel scaffold + list: `SalesSection` nav item, routes,
      `DiscountIndexController` (rows, filters, search, KPI strip), `DiscountsTableExtension`,
      `pages/discounts/Index.vue`, `percent` icon, `discounts.php` + `nav.php` lang keys
      (16 locales), tests.
- [x] Slice 3 — Type seam + edit shell (shipped with slice 2 — a list whose rows
      have nowhere to open is not shippable on its own): `DiscountTypeForm` contract,
      `Section::discountTypeForms()`, `DiscountTypeSchema`, `DiscountDraftResource`,
      `DiscountCreateController` / `DiscountEditController`,
      `pages/discounts/{Create,Edit}.vue` with Details / Schedule / Availability / Usage and
      the `RawDataForm` fallback, tests.
- [x] Slice 4 — First-party type forms + conditions: `PercentageOffForm`,
      `FixedAmountOffForm`, `BuyXGetYForm`, the conditions block, currency scaling through
      `PriceCalculator::toMinor()`, tests (including a zero-decimal currency).
- [x] Slice 5 — Targeting: `targets.search` endpoint, `TargetChipList`,
      `TargetPickerDialog`, per-bucket blocks driven by `targetBuckets()`, the eligible-
      customers card, `ui.ts` exports, tests.
- [ ] Slice 6 — Seam proof: deferred to the free-shipping spec, as this plan allowed.
      The seam is already proven against a type registered from outside core by
      `tests/panel/Feature/Discounts/DiscountTypeSeamTest`, whose fixture exercises a
      custom component, narrowed buckets, a scaling round trip and its own rules. What
      `ShippingDiscount` would add beyond that is mostly packaging — an npm workspace,
      a Vite config, a committed bundle, CI wiring and `de` / `nl` locales — and the
      open question below may move the type into core, taking its form with it. Until
      it lands, `table-rate-shipping` users get the `RawDataForm` fallback.
- [ ] Deferred — core free-shipping type (own spec), `restriction` column removal,
      normalising the three targeting tables.
