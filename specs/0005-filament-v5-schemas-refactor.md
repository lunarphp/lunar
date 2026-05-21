# 0005 — Filament v5 schemas refactor

- Status: completed
- Author: Glenn Jacobs
- Created: 2026-05-21
- TODO item: "Filament v5 schemas refactor"

## Problem

Spec [[0004-filament-v5-upgrade]] bumped the admin to Filament v5 with the smallest possible diff — the resource classes still look v4-shaped. Concretely:

- Every resource builds its form/table inline (or via private helper methods on the resource class itself), giving 200–500 line resource files. `ProductResource.php` is 466 lines; `OrderResource.php` is 267.
- Lunar's `BaseResource` wraps Filament's `form()` / `table()` with `extendForm` / `extendTable` hooks via the `Support/Resources/Concerns/Extends*` traits, then delegates to `getDefaultForm(Schema $schema)` / `getDefaultTable(Table $table)` instead of Filament's idiomatic `form(Schema $schema)` / `table(Table $table)`.
- Per-component override hooks (`getMainFormComponents()`, `getNameFormComponent()`, `getTableColumns()`, etc.) sprawl across `BaseResource` subclasses without a clear contract.
- Filament v5's generator (`php artisan make:filament-resource`) ships the **split pattern** by default — `Resources/{Resource}/Schemas/{Resource}Form.php`, `Resources/{Resource}/Schemas/{Resource}Infolist.php`, `Resources/{Resource}/Tables/{Resource}Table.php`. Hand-written Lunar resources do not match what the generator produces.

The cost of staying in the v4 shape: every new resource (first-party or downstream) has to learn Lunar's bespoke `getDefaultForm` + hook convention instead of the convention Filament's own docs and stubs teach. The mismatch will widen as Filament v5 matures.

## Proposal

Restructure every Lunar Filament resource to match Filament v5's generator conventions, with Lunar's extension hooks rebuilt at the new seams.

Scope:

- **Adopt the split-class layout** for every resource:
  - `Filament/Resources/{Resource}.php` — thin entry: `getModel`, `getLabel`, navigation metadata, page registration, and one-line delegations to the split classes.
  - `Filament/Resources/{Resource}/Schemas/{Resource}Form.php` — `configure(Schema $schema): Schema` static method holding the form definition.
  - `Filament/Resources/{Resource}/Schemas/{Resource}Infolist.php` — only where the resource ships an infolist.
  - `Filament/Resources/{Resource}/Tables/{Resource}Table.php` — `configure(Table $table): Table`.
  - Existing `Pages/`, `Widgets/`, `RelationManagers/` directories stay put — they already match the v5 convention.

- **Normalize Filament method names** on the resource:
  - `getDefaultForm(Schema $schema)` → `form(Schema $schema)` (Filament's native entry point).
  - `getDefaultTable(Table $table)` → `table(Table $table)`.
  - `getDefaultInfolist(Schema $schema)` → `infolist(Schema $schema)` (where present).
  - The new `form`/`table`/`infolist` methods delegate to the new split classes: `return {Resource}Form::configure($schema);` etc.

- **Rebuild Lunar's extension surface** at the new seam — two patterns, both supported:

  **Primary (add-ons / plugins): hook-based, stackable.** Keep `LunarPanel::extensions([…])` as the first-class surface. Add-on authors register an extension class against a split-class target:

  ```php
  LunarPanel::extensions([
      BrandForm::class => [WarrantyFieldsExtension::class, AuditFieldsExtension::class],
      BrandTable::class => WarrantyColumnExtension::class,
  ]);
  ```

  The extension class exposes methods matching the split class's hook points (e.g. `configureForm(Schema $schema): Schema`). Multiple extensions stack in registration order — Plugin A and Plugin B can both add fields to the same form without either knowing about the other. The hook lives on the split class (`{Resource}Form` / `{Resource}Table` / `{Resource}Infolist`) rather than on the resource, making the extension target obvious.

  **Escape hatch (apps): subclass-and-rebind.** When an app needs to fully replace a form/table, subclass the split class and bind the replacement via the panel:

  ```php
  class AcmeBrandForm extends BrandForm
  {
      public static function configure(Schema $schema): Schema
      {
          return parent::configure($schema)->components([…]);
      }
  }

  LunarPanel::panel(fn (Panel $panel) => $panel
      ->resource(BrandResource::class, schemas: [BrandForm::class => AcmeBrandForm::class])
  );
  ```

  Reserved for app-level customization where a single owner controls the full diff — does not stack with other extensions.

  **Mechanical changes on `BaseResource`:**
  - Drop the `Extends{Forms,Tables,Pages,RelationManagers,Subnavigation}` traits — the hook indirection moves onto the split classes.
  - `BaseResource::form()` becomes a one-line delegate to `{Resource}Form::configure()`; the `callStaticLunarHook` call moves into `{Resource}Form::configure()` itself.
  - `LunarPanel::extensions([…])` registration target migrates from `{Resource}::class` to `{Resource}Form::class` / `{Resource}Table::class`. Rector rule in [[0001-upgrade-package]] handles the rewrite.
  - Method names on the extension class change from `extendForm` / `extendTable` to `configureForm` / `configureTable` to match the new split-class entry point. Rector covers the rename.

- **Apply Pages refactor in lockstep** — the 17 base/page concerns under `Support/Pages/Concerns/` (`ExtendsForms`, `ExtendsTables`, `ExtendsHeaderActions`, `ExtendsFormActions`, `ExtendsHeaderWidgets`, `ExtendsFooterWidgets`, `ExtendsHeadings`, `ExtendsTabs`, `ExtendsTablePagination`, `ExtendsInfolist`) become individual override methods on the page base class. Same migration story as resources.

- **Cover both packages**:
  - 21 resources under `packages/admin/src/Filament/Resources/` (and their child folders).
  - 3 resources under `packages/table-rate-shipping/src/Filament/Resources/` (`ShippingZoneResource`, `ShippingMethodResource`, `ShippingExclusionListResource`) — same treatment.

- **Update Lunar's own resource generators / stubs** (if any exist under `packages/admin/src/Console/Commands/`) so `php artisan make:lunar-admin` emits the split pattern.

- **Re-run the admin Pest suite** after each resource is migrated; do not batch.

- **Update the `Support/Resources/BaseResource`** to only carry Lunar-specific resource concerns (permission gating via `getPermission()`, navigation helpers, model-contract resolution). Strip every method that exists only to wrap Filament's native API.

Coordination:

- Lands on top of [[0004-filament-v5-upgrade]] (already complete). Filament v5 + Livewire v4 are already installed, so this spec is pure refactor — no dependency moves.
- Lands **before** the planned "Move core Filament e-commerce components to a new `lunarphp/filament` package" extraction. Extracting after the refactor means the new package is born with v5-idiomatic shape; doing it before doubles the migration work across two packages.
- The upgrade package ([[0001-upgrade-package]]) gains Rector rules for the renamed methods on `BaseResource` so user code that overrode `getDefaultForm` / `getDefaultTable` / `getMainFormComponents` etc. gets rewritten. No deprecation cycle — v2 is the breaking-change window, removals are straight removals.

## Alternatives considered

- **Keep the current shape, just rename `getDefaultForm` → `form`.** Rejected — leaves the 466-line `ProductResource` intact, doesn't get the v5 directory shape, and doesn't match generator output. The bookkeeping work is the same whether we move 50 lines or 500.
- **Adopt schemas only inside the resource class (no Schemas/Tables sub-classes).** Rejected — that's just the rename, and the resource files stay enormous. The generator's default is the split pattern; we should match the default.
- **Preserve every `callStaticLunarHook` hook for backwards compatibility.** Rejected — v2 is the breaking-change window. Carrying both the new override pattern and the old hook layer indefinitely doubles the public surface and confuses extension docs. Rector + a clearly documented migration covers downstream apps.
- **Do this as part of the `lunarphp/filament` extraction spec.** Rejected — bundles two large, independently risky changes. The extraction is a file move; the schemas refactor is a shape change. Easier to review and bisect when split.

## Migration impact

- **Database**: none.
- **Public contract surface**: every resource's overridable method shifts (`getDefaultForm` → `form`, plus per-component helpers move to the new `{Resource}Form` class). `LunarPanel::extensions([…])` is preserved but its targets change from `{Resource}::class` to `{Resource}Form::class` / `{Resource}Table::class`, and the extension method names change from `extendForm` / `extendTable` to `configureForm` / `configureTable`.
- **Upgrade path**: end users who extend a Lunar resource via `LunarPanel::extensions([…])` — the common case — keep the same registration mechanism; only the target class and method names change. Rector rules in [[0001-upgrade-package]] cover both renames. App-level customizations that previously subclassed the whole resource gain a cleaner path: subclass the smaller `{Resource}Form` / `{Resource}Table` and rebind via `Panel::resource(…, schemas: […])`.
- **Translations**: none.
- **Filament / admin**: this _is_ the admin change.
- **Plugins**: third-party plugins are unaffected as long as they wrap Filament's native methods. Anything that hooks into `Lunar\Admin\Support\Resources\Concerns\Extends*` will need to update.

## Open questions

- How does the Pages refactor interact with cluster-scoped pages (`src/Filament/Clusters/Taxes.php` and its child resources)? Cluster pages may need the same `Pages/Schemas/Tables` split treatment. Decidable when we get there.

## Resolved questions

- **Extension model**: hook-based registration via `LunarPanel::extensions([…])` is the primary, recommended pattern for add-ons (supports stacking — multiple plugins can extend the same form/table). Subclass-and-rebind via `Panel::resource(…, schemas: […])` is the documented escape hatch for app-level customization. Both ship; docs lead with hooks.
- **Naming**: split-class entry method is `configure()`, matching Filament v5's generator output verbatim for discoverability against the upstream docs.
- **`BaseResource` shape**: keep `BaseResource extends Resource` as the entry point; compose Lunar concerns via small traits in `Support/Resources/Concerns/`, mirroring Filament's own `Resource.php` (143 lines composing 11 traits). The wrapper traits (`ExtendsForms`, `ExtendsTables`, `ExtendsPages`, `ExtendsRelationManagers`, `ExtendsSubnavigation`) are deleted; the actual Lunar behaviour inline on `BaseResource` today is extracted into named concerns: `HasLunarPermissions` (permission gate + `can()` + `registerNavigationItems()`), `ResolvesModelContract` (interface-aware `getModel()`), `HasScoutGlobalSearch` (Scout-aware global search). Result: `BaseResource` itself drops to ~15 lines.
- **Granular helpers**: keep the per-component helpers (`getNameFormComponent`, `getSkuFormComponent`, `getNameTableColumn`, etc.) as `public static` methods on the new `{Resource}Form` / `{Resource}Table` classes. Audit found real cross-resource reuse — e.g. `BrandResource/Pages/ManageBrandProducts.php` reuses `ProductResource::getNameTableColumn()` and `getSkuTableColumn()`; `ProductResource/Pages/ListProducts.php` reuses `getBaseNameFormComponent()` et al for its create modal. These helpers also become the cleanest extension target: an add-on can override `getSkuFormComponent()` to replace just the SKU field rather than mutating the whole built schema.
- **PR slicing**: one PR per resource family. Catalog (Brand, Product, ProductOption, ProductType, ProductVariant, Collection, CollectionGroup, Tag, Attribute, AttributeGroup), Sales (Order, Customer, CustomerGroup, Discount), Settings (Channel, Currency, Language, Staff, TaxClass, TaxRate, TaxZone, Activity). Shipping resources land as their own PR. Each PR is reviewable in isolation; the `BaseResource` + concerns trim lands in the first PR.

## Progress

**Merged** (PR #2481, branched off `2.x`):

- Catalog family — 9 resources migrated to the split-class layout: Brand, Product, Tag, Collection, CollectionGroup, ProductType, ProductOption, ProductVariant, AttributeGroup. Each resource keeps metadata, page registration, navigation, and global search overrides; form/table components live in `{Resource}/Schemas/{Resource}Form.php` and `{Resource}/Tables/{Resource}Table.php`. Granular component helpers retained as `public static` methods on the split classes (audit confirmed real cross-resource consumers).
- `BaseResource` trimmed to compose three named concerns: `HasLunarPermissions`, `ResolvesModelContract`, `HasScoutGlobalSearch`. Wrapper traits (`ExtendsForms`, `ExtendsTables`, `ExtendsPages`, `ExtendsRelationManagers`, `ExtendsSubnavigation`) remain in place for unmigrated resources; deleted when no caller depends on them.
- Hook contract on migrated resources: `LunarPanel::extensions([{Resource}Form::class => …])` with `configureForm` / `configureTable` method names. `ResourceExtensionTest` exercises both legacy and new patterns.
- Cross-resource consumer call sites updated in lockstep: `BrandResource/Pages/ManageBrandProducts`, 3× `ProductVariantResource/Pages/Manage*`, 2× `ProductResource/Pages/Manage*`, `Support/Concerns/Products/ManagesProductPricing`.

**Outstanding**:

- **Sales family**: `OrderResource`, `CustomerResource`, `CustomerGroupResource`, `DiscountResource`. Order has known cross-consumers (`LatestOrdersTable` widget, `OrdersRelationManager` on Customer). Discount has `ListDiscounts` consuming `getNameFormComponent`.
- **Settings family**: `ChannelResource`, `CurrencyResource`, `LanguageResource`, `StaffResource`, `TaxClassResource`, `TaxRateResource`, `TaxZoneResource`, `ActivityResource`.
- **Shipping**: 3 resources in `packages/table-rate-shipping/src/Filament/Resources/` (`ShippingZoneResource`, `ShippingMethodResource`, `ShippingExclusionListResource`).
- **Pages refactor** (spec § Apply Pages refactor in lockstep): the 10 page-extension traits under `Support/Pages/Concerns/` still need rebuilding at the new seam. Deferred until all resources are migrated so the Page-base shape lands once.
- **Cluster pages**: `src/Filament/Clusters/Taxes.php` and its children — pattern decided when Settings/Tax resources are touched.
- **Wrapper-trait removal**: once every resource above is migrated, delete the five `Extends*` traits on `BaseResource` and add Rector rules in `lunarphp/upgrade` for the renamed methods (`getDefaultForm` → `form`, `getDefaultTable` → `table`, etc.).

## References

- `packages/lunar/packages/admin/src/Filament/Resources/` — 21 resources to migrate
- `packages/lunar/packages/admin/src/Support/Resources/BaseResource.php` — base class to trim
- `packages/lunar/packages/admin/src/Support/Resources/Concerns/` — 5 trait files to remove or rebuild
- `packages/lunar/packages/admin/src/Support/Pages/Concerns/` — 10 page-extension traits to refactor
- `packages/lunar/packages/table-rate-shipping/src/Filament/Resources/` — 3 additional resources
- `vendor/filament/filament/src/Commands/FileGenerators/Resources/` — v5 generator source; the canonical reference for shape
- Filament v5 schemas overview: https://filamentphp.com/docs/5.x/schemas/overview
- Filament v5 resources overview: https://filamentphp.com/docs/5.x/resources/overview
- [[0001-upgrade-package]] (completed) — Rector host for the `BaseResource` method renames
- [[0004-filament-v5-upgrade]] (in progress on `feature/0004-filament-v5`) — must land first
- TODO: "Move core Filament e-commerce components to a new `lunarphp/filament` package" — must land **after** this spec
