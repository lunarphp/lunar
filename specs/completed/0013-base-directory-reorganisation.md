# 0013 — `Base/` directory reorganisation

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: (new — to be added under "Outstanding")

## Problem

`packages/core/src/Base/` started in v1 as a home for the abstract building blocks of the package — base model, modifiers, traits, manager interfaces. As v1 progressed it became a catch-all: any class that didn't fit a domain folder landed there. In v2, post the spec 0002 namespace move, `Lunar\Core\Base\*` now contains 54 files spanning eight semantic categories, several of which **already have a canonical home elsewhere in `packages/core/src/`**.

### Concrete signals

**1. `Base/` duplicates top-level folders that already exist:**

| `Base/` sub-path | Existing equivalent at `packages/core/src/` |
| --- | --- |
| `Base/DataTransferObjects/` (7 files) | `DataObjects/` |
| `Base/Validation/` (2 files) | `Validation/` |
| `Base/FieldType.php` + `Base/FieldTypeManifest.php` + `Base/FieldTypeManifestInterface.php` | `FieldTypes/` |
| All `Base/*Interface.php` + role contracts (`Addressable`, `Purchasable`, `LunarUser`, `MediaDefinitionsInterface`, `ProvidesTelemetryInsights`, `HasThumbnailImage`) | `Contracts/` |

A reader looking for a payment DTO has to know that "DTOs live in `Base/DataTransferObjects/`, not `DataObjects/`" — there is no rule, only history.

**2. Behaviour traits sit in `Base/Traits/` when the package convention says `Concerns/`:**

`Base/Traits/` contains 18 model traits (`HasPrices`, `HasMedia`, `HasChannels`, `HasTranslations`, `HasCustomerGroups`, …). The package's own `CLAUDE.md` already declares `Concerns/` as one of the two trait homes ("Treat anything outside `Concerns/`, `Support/`, and internal namespaces as a contract") — but `packages/core/src/Concerns/` does not yet exist. The convention is documented; the directory is not.

**3. Naming collisions across sub-folders:**

- `Base/LunarUser.php` (the contract interface) and `Base/Traits/LunarUser.php` (the trait that implements it) share a name. A `use Lunar\Core\Base\LunarUser;` is ambiguous on a first read — you have to open the file to know if it is the interface or the trait.
- `Base/Casts/DiscountBreakdown.php`, `Base/Casts/ShippingBreakdown.php`, `Base/Casts/TaxBreakdown.php` each have a value-object twin in `Base/ValueObjects/Cart/` with the same class name. The cast wraps the VO — the proximity is intentional but the duplicated names make code search noisy.

**4. Mixed levels of abstraction in one folder:**

`Base/` flat root currently holds, side-by-side:

- Pure contracts: `Addressable`, `Purchasable`, `CartSessionInterface`, `*ManagerInterface` (eight of them)
- Abstract base classes: `BaseModel`, `Migration`, `CartModifier`, `OrderModifier`, `ShippingModifier`, `TaxDriver`
- Concrete services: `TelemetryService`, `OrderReferenceGenerator`, `StandardMediaDefinitions`, the `*Manifest` classes
- Collections: `CartModifiers`, `OrderModifiers`, `ShippingModifiers` (Laravel `Collection` subclasses)

There is no semantic glue between `Addressable` (a contract a `Cart` implements) and `TelemetryService` (a concrete singleton that ships telemetry payloads).

**5. Footprint:**

`Lunar\Core\Base\*` is referenced from 491 distinct `use` statements across this monorepo (431 in `core`, 33 in `table-rate-shipping`, the rest in payment + admin + filament + upgrade). Every move costs a Rector entry — but every move is also a `use` line a reader will not have to chase.

### Why now

v2 is the only window where the `Lunar\Base\*` → `Lunar\Core\Base\*` rename has already paid the "every import changes" cost (spec 0002) but `Lunar\Core\Base\*` has not yet shipped a stable. Folding a second rename pass into the same major version means downstream consumers run one Rector set, not two.

## Proposal

Drain `Base/` by relocating each class to the directory that already names its concern. The `Base/` namespace itself does not survive.

Storage path (`packages/core/src/Base/...`) and PSR-4 namespace (`Lunar\Core\Base\...`) move together for every class. Each move is registered in `Lunar\Upgrade\Rector\LunarSetList::V1_TO_V2_CLASS_RENAMES` (the existing v1 entries stay; we add v1→v2 rows pointing at the new home).

### Target layout

```
packages/core/src/
├── Casts/                 ← from Base/Casts/
├── Concerns/              ← from Base/Traits/  (new directory; convention already declared)
├── Contracts/             ← merge: every Base/*Interface.php and role contract
├── DataObjects/           ← merge: Base/DataTransferObjects/* folded in
├── Enums/                 ← from Base/Enums/
├── FieldTypes/            ← merge: Base/FieldType.php + Base/FieldTypeManifest*.php
├── Manifests/             ← from Base/{Attribute,Model,Shipping}Manifest*.php  (new)
├── Modifiers/             ← from Base/{Cart,CartLine,Order,Shipping}Modifier(s).php  (new)
├── Models/Base.php        ← from Base/BaseModel.php
├── Telemetry/             ← Base/TelemetryService*.php + Base/TelemetryInsights.php
├── Validation/            ← merge: Base/Validation/CouponValidator*.php folded in
└── ValueObjects/          ← from Base/ValueObjects/
```

### Per-file destination

#### `Base/Traits/*` → `Concerns/`

Straight move + namespace change:

| From | To |
| --- | --- |
| `Lunar\Core\Base\Traits\CachesProperties` | `Lunar\Core\Concerns\CachesProperties` |
| `Lunar\Core\Base\Traits\CanScheduleAvailability` | `Lunar\Core\Concerns\CanScheduleAvailability` |
| `Lunar\Core\Base\Traits\HasAttributes` | `Lunar\Core\Concerns\HasAttributes` |
| `Lunar\Core\Base\Traits\HasChannels` | `Lunar\Core\Concerns\HasChannels` |
| `Lunar\Core\Base\Traits\HasCustomerGroups` | `Lunar\Core\Concerns\HasCustomerGroups` |
| `Lunar\Core\Base\Traits\HasDefaultRecord` | `Lunar\Core\Concerns\HasDefaultRecord` |
| `Lunar\Core\Base\Traits\HasDimensions` | `Lunar\Core\Concerns\HasDimensions` |
| `Lunar\Core\Base\Traits\HasMacros` | `Lunar\Core\Concerns\HasMacros` |
| `Lunar\Core\Base\Traits\HasMedia` | `Lunar\Core\Concerns\HasMedia` |
| `Lunar\Core\Base\Traits\HasModelExtending` | `Lunar\Core\Concerns\HasModelExtending` |
| `Lunar\Core\Base\Traits\HasPersonalDetails` | `Lunar\Core\Concerns\HasPersonalDetails` |
| `Lunar\Core\Base\Traits\HasPrices` | `Lunar\Core\Concerns\HasPrices` |
| `Lunar\Core\Base\Traits\HasTags` | `Lunar\Core\Concerns\HasTags` |
| `Lunar\Core\Base\Traits\HasTranslations` | `Lunar\Core\Concerns\HasTranslations` |
| `Lunar\Core\Base\Traits\HasUrls` | `Lunar\Core\Concerns\HasUrls` |
| `Lunar\Core\Base\Traits\LogsActivity` | `Lunar\Core\Concerns\LogsActivity` |
| `Lunar\Core\Base\Traits\LunarUser` | `Lunar\Core\Concerns\IsLunarUser` (renamed — see below) |
| `Lunar\Core\Base\Traits\Searchable` | `Lunar\Core\Concerns\Searchable` |

The `LunarUser` trait is renamed to `IsLunarUser` so it does not collide with the `LunarUser` contract once both live at peer-level paths. Existing downstream `use Lunar\Base\Traits\LunarUser;` is rewritten by Rector to `use Lunar\Core\Concerns\IsLunarUser;`. Inside the trait, no behavioural change.

#### `Base/*Interface.php` and role contracts → `Contracts/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\Addressable` | `Lunar\Core\Contracts\Addressable` |
| `Lunar\Core\Base\AttributeManifestInterface` | `Lunar\Core\Contracts\AttributeManifest` |
| `Lunar\Core\Base\CartSessionInterface` | `Lunar\Core\Contracts\CartSession` |
| `Lunar\Core\Base\DiscountManagerInterface` | `Lunar\Core\Contracts\DiscountManager` |
| `Lunar\Core\Base\DiscountTypeInterface` | `Lunar\Core\Contracts\DiscountType` |
| `Lunar\Core\Base\FieldTypeManifestInterface` | `Lunar\Core\Contracts\FieldTypeManifest` |
| `Lunar\Core\Base\HasThumbnailImage` | `Lunar\Core\Contracts\HasThumbnailImage` |
| `Lunar\Core\Base\LunarUser` | `Lunar\Core\Contracts\LunarUser` |
| `Lunar\Core\Base\MediaDefinitionsInterface` | `Lunar\Core\Contracts\MediaDefinitions` |
| `Lunar\Core\Base\ModelManifestInterface` | `Lunar\Core\Contracts\ModelManifest` |
| `Lunar\Core\Base\OrderReferenceGeneratorInterface` | `Lunar\Core\Contracts\OrderReferenceGenerator` |
| `Lunar\Core\Base\PaymentManagerInterface` | `Lunar\Core\Contracts\PaymentManager` |
| `Lunar\Core\Base\PaymentTypeInterface` | `Lunar\Core\Contracts\PaymentType` |
| `Lunar\Core\Base\PricingManagerInterface` | `Lunar\Core\Contracts\PricingManager` |
| `Lunar\Core\Base\ProvidesTelemetryInsights` | `Lunar\Core\Contracts\ProvidesTelemetryInsights` |
| `Lunar\Core\Base\Purchasable` | `Lunar\Core\Contracts\Purchasable` |
| `Lunar\Core\Base\ShippingManifestInterface` | `Lunar\Core\Contracts\ShippingManifest` |
| `Lunar\Core\Base\StorefrontSessionInterface` | `Lunar\Core\Contracts\StorefrontSession` |
| `Lunar\Core\Base\TaxManagerInterface` | `Lunar\Core\Contracts\TaxManager` |
| `Lunar\Core\Base\TelemetryServiceInterface` | `Lunar\Core\Contracts\TelemetryService` |

The `*Interface` suffix is dropped: a class living under `Contracts/` is unambiguously a contract, so the suffix only adds visual noise (this matches the existing `Models/Contracts/` style already used in this package, e.g. `Lunar\Core\Models\Contracts\Currency`).

Concrete implementations of each manager interface (in `packages/core/src/Managers/`) keep their current names; their `implements` clauses are rewritten by Rector.

#### `Base/Casts/*` → `Casts/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\Casts\AsAttributeData` | `Lunar\Core\Casts\AsAttributeData` |
| `Lunar\Core\Base\Casts\CouponString` | `Lunar\Core\Casts\CouponString` |
| `Lunar\Core\Base\Casts\DiscountBreakdown` | `Lunar\Core\Casts\DiscountBreakdown` |
| `Lunar\Core\Base\Casts\ShippingBreakdown` | `Lunar\Core\Casts\ShippingBreakdown` |
| `Lunar\Core\Base\Casts\TaxBreakdown` | `Lunar\Core\Casts\TaxBreakdown` |

Note: the spec 0012 `Lunar\Core\Base\Casts\Price` cast (which spec 0012 deleted) is **not** part of this move — its Rector row remains pointing at the spec 0012 replacement.

#### `Base/DataTransferObjects/*` → `DataObjects/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\DataTransferObjects\CartDiscount` | `Lunar\Core\DataObjects\CartDiscount` |
| `Lunar\Core\Base\DataTransferObjects\PaymentAuthorize` | `Lunar\Core\DataObjects\PaymentAuthorize` |
| `Lunar\Core\Base\DataTransferObjects\PaymentCapture` | `Lunar\Core\DataObjects\PaymentCapture` |
| `Lunar\Core\Base\DataTransferObjects\PaymentCheck` | `Lunar\Core\DataObjects\PaymentCheck` |
| `Lunar\Core\Base\DataTransferObjects\PaymentChecks` | `Lunar\Core\DataObjects\PaymentChecks` |
| `Lunar\Core\Base\DataTransferObjects\PaymentRefund` | `Lunar\Core\DataObjects\PaymentRefund` |
| `Lunar\Core\Base\DataTransferObjects\PricingResponse` | `Lunar\Core\DataObjects\PricingResponse` |

Audit before merging: confirm there are no name collisions between `DataTransferObjects/` and the existing `DataObjects/` contents. Resolve any by prefixing with the domain (e.g. `Payment` already disambiguates the payment four).

#### `Base/Enums/*` → `Enums/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\Enums\ProductAssociation` | `Lunar\Core\Enums\ProductAssociation` |
| `Lunar\Core\Base\Enums\Concerns\ProvidesProductAssociationType` | `Lunar\Core\Enums\Concerns\ProvidesProductAssociationType` |

#### `Base/ValueObjects/*` → `ValueObjects/`

The `Cart/` sub-namespace is preserved:

| From | To |
| --- | --- |
| `Lunar\Core\Base\ValueObjects\Cart\DiscountBreakdown` | `Lunar\Core\ValueObjects\Cart\DiscountBreakdown` |
| `Lunar\Core\Base\ValueObjects\Cart\DiscountBreakdownLine` | `Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine` |
| `Lunar\Core\Base\ValueObjects\Cart\FreeItem` | `Lunar\Core\ValueObjects\Cart\FreeItem` |
| `Lunar\Core\Base\ValueObjects\Cart\Promotion` | `Lunar\Core\ValueObjects\Cart\Promotion` |
| `Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown` | `Lunar\Core\ValueObjects\Cart\ShippingBreakdown` |
| `Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdownItem` | `Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem` |
| `Lunar\Core\Base\ValueObjects\Cart\TaxBreakdown` | `Lunar\Core\ValueObjects\Cart\TaxBreakdown` |
| `Lunar\Core\Base\ValueObjects\Cart\TaxBreakdownAmount` | `Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount` |

#### `Base/Validation/*` → `Validation/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\Validation\CouponValidator` | `Lunar\Core\Validation\CouponValidator` |
| `Lunar\Core\Base\Validation\CouponValidatorInterface` | `Lunar\Core\Contracts\CouponValidator` |

The interface joins the other contracts and drops the `Interface` suffix; the concrete validator goes to `Validation/`.

#### `Base/FieldType*.php` → `FieldTypes/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\FieldType` | `Lunar\Core\FieldTypes\FieldType` |
| `Lunar\Core\Base\FieldTypeManifest` | `Lunar\Core\FieldTypes\Manifest` |
| `Lunar\Core\Base\FieldTypeManifestInterface` | `Lunar\Core\Contracts\FieldTypeManifest` (per the contracts table above) |

The `FieldType` prefix is dropped from the manifest class because the surrounding `FieldTypes/` namespace already supplies that context; the result is symmetrical with `Addons\Manifest`, `Auth\Manifest`, etc.

#### `Base/{Cart,CartLine,Order,Shipping}Modifier(s).php` → `Modifiers/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\CartModifier` | `Lunar\Core\Modifiers\CartModifier` |
| `Lunar\Core\Base\CartModifiers` | `Lunar\Core\Modifiers\CartModifiers` |
| `Lunar\Core\Base\CartLineModifier` | `Lunar\Core\Modifiers\CartLineModifier` |
| `Lunar\Core\Base\CartLineModifiers` | `Lunar\Core\Modifiers\CartLineModifiers` |
| `Lunar\Core\Base\OrderModifier` | `Lunar\Core\Modifiers\OrderModifier` |
| `Lunar\Core\Base\OrderModifiers` | `Lunar\Core\Modifiers\OrderModifiers` |
| `Lunar\Core\Base\ShippingModifier` | `Lunar\Core\Modifiers\ShippingModifier` |
| `Lunar\Core\Base\ShippingModifiers` | `Lunar\Core\Modifiers\ShippingModifiers` |

#### `Base/*Manifest.php` (concrete) → `Manifests/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\AttributeManifest` | `Lunar\Core\Manifests\AttributeManifest` |
| `Lunar\Core\Base\ModelManifest` | `Lunar\Core\Manifests\ModelManifest` |
| `Lunar\Core\Base\ShippingManifest` | `Lunar\Core\Manifests\ShippingManifest` |

(`FieldTypeManifest` and `AttributeManifest` etc. all share the manifest pattern but the field-type one lives next to the field types it indexes, so it goes to `FieldTypes/Manifest`. The other three remain at the root because they cross domains.)

#### `Base/Telemetry*` and `Base/TelemetryInsights.php` → `Telemetry/`

| From | To |
| --- | --- |
| `Lunar\Core\Base\TelemetryService` | `Lunar\Core\Telemetry\TelemetryService` |
| `Lunar\Core\Base\TelemetryServiceInterface` | `Lunar\Core\Contracts\TelemetryService` (per the contracts table) |
| `Lunar\Core\Base\TelemetryInsights` | `Lunar\Core\Telemetry\Insights` |
| `Lunar\Core\Base\ProvidesTelemetryInsights` | `Lunar\Core\Contracts\ProvidesTelemetryInsights` |

#### `Base/BaseModel.php`, `Base/Migration.php`, etc.

| From | To |
| --- | --- |
| `Lunar\Core\Base\BaseModel` | `Lunar\Core\Models\Base` |
| `Lunar\Core\Base\Migration` | `Lunar\Core\Database\Migration` |
| `Lunar\Core\Base\StandardMediaDefinitions` | `Lunar\Core\Media\StandardDefinitions` |
| `Lunar\Core\Base\OrderReferenceGenerator` | `Lunar\Core\Orders\ReferenceGenerator` |
| `Lunar\Core\Base\TaxDriver` | `Lunar\Core\Drivers\TaxDriver` (joining `Drivers/SystemTaxDriver`) |

Renaming `BaseModel` → `Models\Base` is the most visible move (every package's `Models/*` extends it). Rector covers the rename; downstream `extends BaseModel` becomes `extends Base` after import, which reads naturally.

### Implementation steps

1. Add `Lunar\Core\Concerns\` (new directory) and `Lunar\Core\Modifiers\` (new directory) to `packages/core/src/`.
2. Move files one category at a time, in this order — each step is a self-contained PR:
   1. Casts
   2. Enums
   3. ValueObjects
   4. DataObjects (DTO merge)
   5. Validation merge
   6. FieldType consolidation
   7. Modifiers
   8. Manifests
   9. Telemetry
   10. Contracts (every `*Interface` and role contract)
   11. Concerns (traits)
   12. Tail: `BaseModel`, `Migration`, `StandardMediaDefinitions`, `OrderReferenceGenerator`, `TaxDriver`
3. For each category, in the same PR:
   - Move the files and update their namespaces.
   - Update `use` statements across every sub-package via `vendor/bin/rector process` against a local rule, or with `gsed`.
   - Append the rename rows to `Lunar\Upgrade\Rector\LunarSetList::V1_TO_V2_CLASS_RENAMES`.
   - Run `vendor/bin/pint --dirty --format agent`, `php artisan test --compact`, `vendor/bin/phpstan analyse --no-progress`.
4. After step 2.12, delete `packages/core/src/Base/`. Verify with `grep -r "Lunar\\\\Core\\\\Base" packages/` returning zero matches.
5. Add `CHANGELOG.md` entries per PR and a roll-up entry on the v2.0 release notes.

### Documentation

- `packages/lunar/CLAUDE.md` — replace the existing convention note with explicit folder responsibilities (`Concerns/` for behaviour traits, `Contracts/` for interfaces, etc.).
- `packages/lunar/packages/core/README.md` — short top-of-tree map of `packages/core/src/`.
- The Lunar v2 documentation site — update any link that points into `Lunar\Base\*` or `Lunar\Core\Base\*`.

## Alternatives considered

**Do nothing.** `Base/` keeps working — the PSR-4 namespace resolves, the classes load, tests pass. The cost is paid by every reader who has to learn two homes for half the categories of class in core (`DataObjects/` *and* `DataTransferObjects/`, `Validation/` *and* `Base/Validation/`, `Concerns/` aspirational *and* `Base/Traits/` actual). Rejected because the v2 major is the only window where the rewrite cost is shared with consumers already running Rector.

**Promote `Base/` to first-class and remove the duplicates.** Move `DataObjects/` → `Base/DataObjects/`, `Validation/` → `Base/Validation/`, etc. Rejected because `Base` is not a useful name — it describes the position in an inheritance hierarchy, not a concern. Every class in `packages/core/src/` is "base" in some sense; the prefix carries no information. Picking a semantic name per concern (the proposal above) makes call sites self-documenting.

**Move everything to `Support/`.** `Support/` already exists at root and is a common Laravel pattern. Rejected because `Support/` is for genuinely cross-cutting helpers — moving 54 files into it would re-create the dumping-ground problem under a different name.

**Reorganise without renaming the interfaces.** Keep `*Interface` suffixes when moving to `Contracts/`. Rejected because (a) the existing `Models/Contracts/*` style in this package already drops the suffix, and (b) doing the suffix rename in the same Rector pass means consumers pay one rewrite, not two.

**Split into a separate `lunarphp/foundation` package.** Extract `Base/`-style primitives into a new dependency. Rejected as out of scope and premature — there is no second consumer of these primitives outside the existing core, and a new published package brings its own release/version-pinning cost.

## Migration impact

### Database migrations

None — this is a pure code-organisation change. No schema, no data, no `migrations` ledger touch.

### Breaking changes to the public contract surface

Every move is a class-string break. Quantified scope (as measured against the current branch):

- **491** `use Lunar\Core\Base\…` statements across the monorepo to be rewritten in lock-step with each PR.
- **~54** Rector rename rows added to `Lunar\Upgrade\Rector\LunarSetList::V1_TO_V2_CLASS_RENAMES`.
- **1** trait rename with a class-name change (`LunarUser` → `IsLunarUser`) — covered by Rector's `RenameClassRector` because it operates on the FQCN.
- **~20** interface renames that drop the `Interface` suffix — also covered by `RenameClassRector`.

### Upgrade path for v1.x consumers

The upgrade package (`lunarphp/upgrade`) is the migration channel. For every move:

1. Append the v1 FQCN → new v2 FQCN row to `LunarSetList::V1_TO_V2_CLASS_RENAMES`. The v1 entries already exist (from spec 0002); the new row replaces the spec 0002 target (`Lunar\Core\Base\X`) with the new target (`Lunar\Core\…\X`).
2. Existing consumers running `vendor/bin/rector process --config vendor/lunarphp/upgrade/config/rector.php` pick the moves up automatically — no extra step on the consumer side.
3. Downstream code that uses string-based FQCNs (e.g. `config('lunar.something', \Lunar\Base\X::class)` referenced as `'Lunar\\Base\\X'` literal) is **not** caught by Rector. Audit the config files in `packages/core/config/` for any string-literal FQCN that needs hand-updating; add a `LunarSetList::V1_TO_V2_STRING_RENAMES` array if the upgrade package does not already cover this case.

### Translation / locale impact

None. No user-facing strings change.

### Filament / admin impact

`packages/admin/` and `packages/filament/` import from `Lunar\Core\Base\*` in 4 + 3 files respectively. Each is rewritten in the same PR that moves the underlying class. No Filament schema, action, or resource needs editing beyond the import line.

### Static analysis

`phpstan.neon.dist` does not pin any `Lunar\Core\Base\*` paths in its `paths`, `excludePaths`, or `ignoreErrors` arrays. The reorganisation needs no phpstan config change.

## Open questions

- **PR granularity.** Twelve PRs (one per category) is the proposed shape — small, reviewable, individually bisectable. Should we instead bundle the lot into a single PR titled "Drain Base/"? One PR is easier to revert as a unit; twelve PRs are easier to review. Owner: Glenn. Resolve before moving to `accepted`.
- **`BaseModel` rename.** `Models\Base` reads naturally after import, but the bare class name `Base` is short and easy to shadow accidentally in test fixtures. Should the class stay as `BaseModel` and only the namespace change (`Lunar\Core\Models\BaseModel`)? Owner: Glenn.
- **`Manifests/` directory.** The three cross-domain manifests (`AttributeManifest`, `ModelManifest`, `ShippingManifest`) are proposed to land in `Manifests/`. An alternative is to colocate each with its manager (`Managers/AttributeManager` + `Managers/AttributeManifest`). Owner: Glenn.
- ~~**TODO ordering.** Spec 0014 (price calculator) is in flight on this branch. This spec touches `Concerns\HasPrices` (via the trait move). Should we sequence ourselves strictly after 0014, or is the trait move trivially safe to run alongside?~~ Resolved: this spec lands **first**. The trait move is a precondition for cleaner pricing-layer namespacing in 0014, and reordering keeps the two specs from racing on `Concerns\HasPrices` in parallel branches.

## References

- [[0002-core-namespace]] — original `Lunar\*` → `Lunar\Core\*` rename and the existing `LunarSetList` catalogue
- [[0001-upgrade-package]] — Rector + step infrastructure that ships every breaking move
- [[0012-price-data-type-refactor]] — touched `Base/Casts/Price` and `Base/Traits/HasPrices`; sets the precedent that breaking moves of `Base/` contents ship as Rector renames
- [[0014-price-calculator]] — sequenced after this spec; consumes the relocated `Concerns\HasPrices`
