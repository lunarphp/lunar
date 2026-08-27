# 0073 — Split `AmountOff` into `PercentageOff` and `FixedAmountOff`

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Split the `AmountOff` discount type into percentage and fixed-amount types (spec 0073)

## Problem

`Lunar\Core\DiscountTypes\AmountOff` is two implementations behind one door. Its `apply()`
reads a `data.fixed_value` boolean and branches to either `applyPercentage()` (a per-line
percentage) or `applyFixedValue()` (a per-currency money amount distributed across lines
with a largest-remainder pass). The two paths share nothing but `getEligibleLines()`.

That flag is invisible to everything that reasons about discount *types*:

- **The type registry is dishonest.** `Discounts::getTypes()` reports one type, "Amount
  Off". Any surface that lists types for selection, or filters a table by type, cannot
  distinguish "15% off" from "GBP 20 off" without reaching into `data` — so a type picker
  cannot describe what the two choices actually do, and a type filter cannot separate them.
- **It blocks registry-driven UI.** [[0072-panel-discounts-section]] renders the discount
  editor from the registry rather than a hardcoded type ladder, so a mode flag hidden inside
  one type's `data` forces exactly the special-casing that design is trying to remove.
- **The name has decayed.** `data.fixed_values` (the per-currency amounts) reads as a plural
  of `data.fixed_value` (the mode flag), which it is not.

Two further problems sit next to it in the same files and are cheapest to fix in the same
pass:

- `Lunar\Filament\Tables\Discount\DiscountTable` renders the type column with
  `formatStateUsing(fn ($state) => (new $state)->getName())` — it instantiates the stored
  class string with no guard. A row whose type class is not installed (an uninstalled
  third-party type today; an unmigrated row after this change) fatals the entire list page
  rather than showing one bad cell.
- `AmountOff::getName()` and `BuyXGetY::getName()` return
  `lunarpanel::discount.form.*.heading` — a translation namespace that no longer exists
  after the Filament bridge extraction. Both type names render as raw key strings today.
- `Lunar\Admin\...\DiscountResource\Pages\ManageBuyXGetYDiscount` is registered nowhere:
  not in `getDefaultPages()`, not in `getDefaultSubNavigation()`, not in any test. It is a
  near-duplicate of `ManageDiscountLimitations` that the split would otherwise have to be
  carried through.

## Proposal

Replace `AmountOff` with two types:

- `Lunar\Core\DiscountTypes\PercentageOff` — `data.percentage`, the current
  `applyPercentage()` body.
- `Lunar\Core\DiscountTypes\FixedAmountOff` — `data.amounts` (minor units keyed by currency
  code), the current `applyFixedValue()` body. Renamed from `fixed_values`, whose name only
  made sense next to the deleted flag.
- `Lunar\Core\DiscountTypes\Concerns\TargetsCartLines` — the current
  `AmountOff::getEligibleLines()` verbatim, used by both. A trait rather than an
  intermediate base class, so cart-level types (`table-rate-shipping`'s `ShippingDiscount`,
  a future free-shipping type) do not inherit line targeting they never use.

`data.fixed_value` is deleted. `AbstractDiscountType` is untouched.
`DiscountManager::$types` and `DiscountFactory` move across.

Both types get real names: new `lunar::discounts.types.*` keys in `packages/core`
(all 16 locales), replacing the dead `lunarpanel::` references in `getName()`.

### Upgrade path

A new one-way data migration in the upgrade package, sequenced after
`2026_06_01_000000_rewrite_lunar_class_strings.php` (which by then has already rewritten
`Lunar\DiscountTypes\AmountOff` to its `Lunar\Core` name). For every discount row of that
type: `type` becomes `FixedAmountOff` when `data->fixed_value` is truthy and `PercentageOff`
otherwise, `data.fixed_values` moves to `data.amounts`, and `data.fixed_value` is dropped.
No `down()` — v1 → v2 data migrations are one-way; restore from backup instead. The
migration is idempotent so a re-run is safe.

For *code* references there is deliberately **no Rector rule**, a departure from the usual
breaking-change requirement. A rename rule would have to guess between the two classes from
a runtime `data` flag and would be wrong roughly half the time, and a silently-wrong rewrite
is worse than a fatal. Instead the `LunarSetList` entry for `AmountOff` is removed — so v1
references fail loudly — and the data-migration step records a warning in its `StepReport`
naming the split, telling the consumer to pick the right class in their own code. Called out
so it reads as a decision, not an oversight.

### Filament / admin changes this forces

The split lands in `core`, but `filament` and `admin` read `AmountOff` and its `data` keys
directly. Enumerated so none of it is discovered late.

**Breaks without changes:**

- `filament` `Schemas/Discount/DiscountForm` — drops the `AmountOff` import and its single
  `Section::make('amount_off')` in favour of one section per type. Its
  `getAmountOffComponents()` becomes `getPercentageOffComponents()` (just `data.percentage`)
  and `getFixedAmountOffComponents()` (per-currency `data.amounts.{code}`); the
  `data.fixed_value` Toggle that switched between them is deleted. These are `public static`
  methods on a published class, so this is a second breaking change in its own right —
  same no-Rector-rule reasoning, documented in the upgrade notes.
- `admin` `DiscountResource/Pages/EditDiscount::mutateFormDataBeforeSave()` — scales
  `$data['data']['fixed_values']` per currency; the key becomes `amounts`.
- `filament` `resources/lang/*/discount.php` (all 16 locales) — `form.amount_off.heading`
  splits into `form.percentage_off.heading` and `form.fixed_amount_off.heading`, and
  `form.fixed_value` is removed. `form.percentage` stays.

**Fragility fixed in the same pass:**

- `filament` `Tables/Discount/DiscountTable` — guard the type column with `class_exists()`
  and fall back to the raw stored string, so one unmigrated or orphaned row degrades to a
  bad cell instead of taking out the list.

**Dead code removed:**

- `admin` `DiscountResource/Pages/ManageBuyXGetYDiscount` — deleted rather than carried
  through the split.

**Deliberate non-change:**

- The Filament resources are not moved onto core action contracts. `UpdatesCollection`,
  `UpdatesBrand` and the rest appear in zero `filament` / `admin` files today — those
  resources save through `BaseEditRecord`'s Eloquent path. Changing that is a pass across
  every resource, not this spec's.

### Testing

- **Pest (`tests/core/`)**: `tests/core/Unit/DiscountTypes/AmountOffTest` splits into
  `PercentageOffTest` and `FixedAmountOffTest`, keeping the existing coverage (including the
  fixed-value remainder pass and the "line already has a better deal" skip). The
  `TargetsCartLines` trait is exercised through both. `DiscountManagerTest` and
  `DiscountManagerMemoisationTest` carry `AmountOff::class` and `fixed_value` fixtures that
  move across. Registry contents assert both types are present.
- **Pest (`tests/upgrade/`)**: the data migration — both branches of `data.fixed_value`, the
  `fixed_values` → `amounts` move, idempotency on re-run, and the report warning.
- **Pest (`tests/admin/`)**: the existing `DiscountResource` page tests still pass (they
  reference only `BuyXGetY`, so no fixture changes), plus a regression covering the
  `DiscountTable` type column against a row whose type class is not installed.
- Pint + PHPStan as required.

## Alternatives considered

- **Keep one type with a percentage/fixed toggle in the form** (the shape Filament has
  today): rejected. It leaves the registry unable to describe or filter the two behaviours,
  which is the actual problem, and preserves a breaking change we are free to make while v2
  is in alpha. The two code paths already share nothing but `getEligibleLines()`.
- **A Rector rule for the split**: rejected on correctness — see the upgrade path above.
- **Keep the `fixed_values` key name** to shrink the data migration: rejected. The migration
  already rewrites the row; renaming one key in the same pass costs nothing, and
  `fixed_values` only parsed as a name next to the `fixed_value` flag being deleted.
- **An intermediate `AbstractLineDiscountType` base class** instead of the
  `TargetsCartLines` trait: rejected. Cart- and shipping-level types extend
  `AbstractDiscountType` too and must not inherit line targeting; a trait composes where a
  second base class would not.
- **Deferring until the panel Discounts section needs it**: rejected. The panel section is
  built registry-driven, so doing this first is what stops the special-casing being written
  and then unwound. Splitting it out of [[0072-panel-discounts-section]] into its own PR
  keeps a breaking core change out of a panel-shaped review.

## Migration impact

- **Database**: no schema changes. One new one-way data migration in the upgrade package.
- **Breaking changes**: `Lunar\Core\DiscountTypes\AmountOff` is removed;
  `data.fixed_value` is dropped and `data.fixed_values` becomes `data.amounts`;
  `DiscountForm::getAmountOffComponents()` is replaced by two methods. Stored rows are
  migrated; code references get an upgrade-report warning rather than a Rector rule.
- **Core public surface**: `PercentageOff`, `FixedAmountOff` and the `TargetsCartLines`
  trait are new; `AmountOff` is removed. `AbstractDiscountType` and the `DiscountType`
  contract are unchanged, so third-party types are unaffected.
- **Upgrade package**: one data migration, one `LunarSetList` entry removed, one report
  warning, plus upgrade notes covering the two code-level breaks.
- **Translations**: new `lunar::discounts.types.*` keys in `core` (16 locales); `filament`
  `discount.php` restructured (16 locales).
- **Filament / admin**: enumerated above.

## Open questions

- **`discounts.restriction`** — a dead column with no reader in any package. It sits in the
  same table and could be dropped under the alpha baseline fold-in rule while this work is
  open. Lean: yes, but as a separate commit so the split stays reviewable on its own.
- **Do the two new types keep `getEligibleLines()` public-ish via the trait**, or should the
  trait be marked internal? It is currently a `protected` method on the type; the trait
  keeps that visibility, so nothing changes — flagging only because the trait is new public
  surface under `DiscountTypes/Concerns/`.

## References

- Core: `DiscountTypes/{AbstractDiscountType,AmountOff,BuyXGetY}`, `Managers/DiscountManager`,
  `Database/Factories/DiscountFactory`, `Models/Discount`.
- Filament / admin: `Schemas/Discount/DiscountForm`, `Tables/Discount/DiscountTable`,
  `DiscountResource/Pages/{EditDiscount,ManageBuyXGetYDiscount}`.
- Upgrade: `Rector/LunarSetList`, `Steps/DataMigrationStep`,
  `database/migrations/2026_06_01_000000_rewrite_lunar_class_strings.php`.
- Third-party type this must not break: `table-rate-shipping`
  `DiscountTypes/ShippingDiscount`.
- [[0072-panel-discounts-section]] — the consumer that motivates the split; it builds on the
  registry this leaves behind.
- [[0014-price-calculator]] / [[0015-pricevalue-arithmetic]] — prior refactors of the
  arithmetic inside these two code paths.

## Implementation plan

- [ ] Slice 1 — Core: `PercentageOff`, `FixedAmountOff`, `TargetsCartLines`, registry +
      factory, `lunar::discounts.types.*` keys (16 locales), core test split.
- [ ] Slice 2 — Filament / admin: `DiscountForm` sections and component methods,
      `EditDiscount` save mutation, `discount.php` across 16 locales, `DiscountTable`
      `class_exists()` guard + regression test, delete `ManageBuyXGetYDiscount`.
- [ ] Slice 3 — Upgrade: the one-way data migration, `LunarSetList` entry removal,
      `StepReport` warning, upgrade notes, tests.
