# 0078 — Table-rate shipping in the Inertia panel

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-09-08
- TODO item: Table-rate shipping in the Inertia panel — Settings > Shipping (zones, methods, exclusion lists), the shipping-discount form, and a Filament-optional package (spec 0078)
- Depends on: [[0072-panel-discounts-section]] (shipped), [[0077-rename-collection-fulfilment-to-pickup]] (vocabulary — this spec is written in post-rename terms)

## Problem

`lunarphp/table-rate-shipping` is the only first-party add-on with a back-office
surface, and that surface is Filament-only. A store running the Inertia panel has no
way to manage shipping zones, methods, rates or exclusion lists without also installing
the Filament admin.

### What the package ships today

- **Three Filament resources** under `src/Filament/Resources/`, registered by
  `ShippingPlugin` from the host app's `LunarPanel::panel(...)` closure:
  `ShippingZoneResource` (edit + `ManageShippingRates` + `ManageShippingExclusions`
  sub-pages), `ShippingMethodResource` (edit + `ManageShippingMethodAvailability` with the
  `AvailabilityScheduleWidget`), and `ShippingExclusionListResource` (edit + a
  `ShippingExclusionRelationManager`). All gated on `shipping:manage`.
- **A discount type**, `ShippingDiscount`, which implements
  `Lunar\Admin\Base\LunarPanelDiscountInterface` to contribute its Filament form. The
  panel's `DiscountTypeSchema` has no mapping for it, so in the panel it falls back to
  `RawDataForm` — a JSON editor. [[0072-panel-discounts-section]] deferred the fix
  ("slice 6") to a follow-up.
- **A hard dependency on Filament.** `composer.json` requires `lunarphp/admin` and
  `filament/filament`; `lunarphp/core` is not even listed. The coupling is real, not
  declarative: `ShippingDiscount` cannot be autoloaded without the admin interface, and
  migration `…000013_add_can_manage_shipping_permission` resolves the guard through
  `Lunar\Admin\Support\Facades\LunarPanel`. Without the admin installed the migration
  silently returns, `shipping:manage` is never seeded, and — because both panels'
  `Gate::after` only grants abilities that exist in the manifest — **nobody, admins
  included, could reach a shipping screen**.
- **No panel code at all.** No `Lunar\Panel` reference, no `package.json`, no Vite
  config, no Vue. Nothing in the panel mentions shipping zones or methods either; the
  panel's only shipping surfaces are the order view (shipping option, carriers,
  fulfilment tracking) and the variant `ShippingCard` (dimensions).

### Smaller things in the way

- The translation namespace is `lunarpanel.shipping`. The panel serves add-on lang groups
  to vue-i18n as `{namespace}::{group}` keys, and vue-i18n treats `.` as a path
  separator, so this namespace cannot be used from a panel page as-is.
- The package carries 14 locales — `de` and `nl` are missing — against the house rule
  of 16.
- `ShippingExclusionFactory`, `ShippingExclusionListFactory` and
  `ShippingZonePostcodeFactory` declare `Lunar\Shipping\Factories`, which is not in the
  PSR-4 map; they resolve only through the dumped classmap.
- `ShippingExclusion::list()` is declared as a `BelongsTo` on `ShippingZone`, which is the
  wrong model.
- There are no tests for any of the Filament shipping screens.

### The first-party template

The panel's settings area is the model. Sixteen settings sections already follow one
shape — a `Section` with `settingsNavigation()` and a `routes()` closure under
`settings/{resource}`, thin controllers rendering `settings/{resource}/{Index,Edit}`, a
`FormRequest`, a `TableExtension` supplying the Edit/Delete row actions, and mutations
delegated to core action contracts. `TaxZonesSection` + `TaxZoneEditController` +
`pages/settings/tax-zones/Edit.vue` is the closest analogue to a shipping zone: one
record, coverage by countries / states / postcodes, nested rate rows, one `useForm`.
`panel-addon-example` shows the same shape from outside the panel package, including the
IIFE build through `@lunarphp/panel-vite-plugin` and page registration through
`window.LunarPanel`.

## Proposal

Give `table-rate-shipping` a **Settings > Shipping** area in the Inertia panel with
feature parity to its Filament resources, register a panel form for `ShippingDiscount`,
and make the package installable with **either** admin — Filament and the panel both
become optional, and core becomes the only hard dependency.

### Package shape

```
packages/table-rate-shipping/
  composer.json                 require: core; suggest: admin, filament, panel
  package.json                  @lunarphp/table-rate-shipping-panel (private, workspace member)
  vite.config.js                lunarPanelPlugin({ name: 'LunarShippingPanel' })
  build/                        compiled IIFE + manifest.json (committed by CI, see below)
  src/
    Actions/                    ShippingZones/, ShippingMethods/, ShippingRates/, ShippingExclusionLists/
    Contracts/Actions/          one interface per action
    Filament/                   existing resources + DiscountForms/ShippingDiscountForm
    Panel/
      ShippingSection.php
      Http/Controllers/         Zone*, Method*, Rate*, ExclusionList*, ExclusionListProductSearch
      Http/Requests/            ZoneRequest, MethodRequest, RateRequest, ExclusionListRequest
      Tables/                   {Zones,Methods,ExclusionLists}TableExtension + Edit/Delete actions
      DiscountTypeForms/ShippingDiscountForm.php
  resources/
    js/
      panel.ts                  registerPages + registerComponents
      pages/settings/shipping/{zones,methods,exclusion-lists}/{Index,Edit}.vue
      components/               RateSlideout, RateTiersEditor, PostcodeListInput,
                                ScheduleGrid, CustomerGroupAvailability, DriverSettings,
                                ShippingDiscountForm
    lang/{16 locales}/          existing groups + nav, zones, methods, exclusion_lists
```

### Dependencies and registration

`composer.json` requires `lunarphp/core` only. `lunarphp/admin`, `filament/filament` and
`lunarphp/panel` move to `suggest`. `ShippingServiceProvider::boot()` registers the panel
section when the panel is present:

```php
if (class_exists(\Lunar\Panel\PanelManager::class)) {
    Panel::section(new ShippingSection);
}
```

Filament registration stays where it is — the host opts in through `ShippingPlugin`, and
the `src/Filament/` classes are never autoloaded otherwise. The two remaining hard
couplings are removed:

- **`ShippingDiscount` stops implementing `LunarPanelDiscountInterface`.** Filament's
  `DiscountForm::configure()` and `EditDiscount`'s fill/save mutators resolve a type's form
  with `instanceof DiscountFormType` on the type instance, so the type class itself has to
  carry the Filament import. The bridge gains the same class-map seam the panel already
  has: `LunarFilament::discountForm(string $type, string $formClass)` on the existing
  `ComponentExtensionsRegistry`, and a `Lunar\Filament\Support\DiscountForms::for(DiscountType $type): ?DiscountFormType`
  resolver that returns the instance itself when it implements the contract (unchanged
  behaviour for every existing type) and otherwise the mapped class. The shipping package
  ships `Filament/DiscountForms/ShippingDiscountForm implements DiscountFormType` with the
  schema and mutators lifted verbatim from the type, registered in
  `ShippingPlugin::register()`.
- **Migration `…000013` resolves the guard through core.**
  `LunarAccessControl::getAuthGuard()` (the core `Auth\Manifest`) replaces the admin
  facade, so `shipping:manage` is seeded whichever admin is installed. `shipping:manage`
  stays a package permission — it is not added to `Manifest::getBasePermissions()`; the
  panel's Roles screen already lists whatever exists in the permissions table, and core's
  `auth.php` already carries its label and description in every locale.

### Actions

The panel never writes models directly, and the package has no actions to delegate to.
`Actions/` + `Contracts/Actions/` are added, bound in `ShippingServiceProvider`, following
the core conventions (one `execute()`, promoted-constructor collaborators, transaction per
write). The Filament pages are **not** moved onto them in this spec — the same call
[[0072-panel-discounts-section]] made for `DiscountResource`.

| Contract | Signature | Notes |
| --- | --- | --- |
| `CreatesShippingZone` | `execute(array $attributes): ShippingZone` | `name`, `type` |
| `UpdatesShippingZone` | `execute(ShippingZone $zone, array $attributes): ShippingZone` | modelled on `UpdateTaxZone`: optional `countries` (ids), `states` (ids), `postcodes` (strings), `exclusion_lists` (ids); a present key replaces that coverage wholesale, an absent key leaves it untouched. Coverage not matching `type` is cleared, as `ShippingZoneForm::syncPostcodes()` does today |
| `DeletesShippingZone` | `execute(ShippingZone $zone): void` | the model's `deleting` hook already cascades |
| `CreatesShippingMethod` | `execute(array $attributes): ShippingMethod` | attaches every customer group `visible + enabled` from now, matching `ListShippingMethod` |
| `UpdatesShippingMethod` | `execute(ShippingMethod $method, array $attributes): ShippingMethod` | columns plus optional `data` (merged key-by-key so a driver's private keys survive), `customer_groups` (`{id, enabled, visible, starts_at, ends_at}` rows replacing the pivot) |
| `DeletesShippingMethod` | `execute(ShippingMethod $method): void` | |
| `SavesShippingRate` | `execute(ShippingZone $zone, ?ShippingRate $rate, array $attributes): ShippingRate` | `shipping_method_id`, `enabled`, `base_prices` (`{currency_code: major-unit}`), `tiers` (`{customer_group_id?, currency_code, min_quantity, price}`); scales through `PriceCalculator::toMinor()` and the currency's `decimal_places`; weight tiers stored as raw integers in the method's `weight_unit`, exactly as `ManageShippingRates::saveShippingRate()` does |
| `DeletesShippingRate` | `execute(ShippingRate $rate): void` | |
| `CreatesShippingExclusionList` / `UpdatesShippingExclusionList` / `DeletesShippingExclusionList` | as the zone trio | `Updates…` takes optional `products` (ids) replacing the `exclusions` rows |

While adding them: `ShippingExclusion::list()` is repointed at `ShippingExclusionList`,
and the three misnamespaced factories move to `Lunar\Shipping\Database\Factories`.

### Panel section

`Lunar\Shipping\Panel\ShippingSection extends Section`, key `shipping`, permission
`shipping:manage` on both the navigation items and the `can:` route middleware.

- **Settings navigation**: a new `shipping` group (label `shipping::nav.shipping`,
  priority 25, between Store and Taxation) with three items — Zones, Methods, Exclusion
  lists. `truck` is the group's icon; the panel's `Icon.vue` set already has it.
- **Routes**, all inside the panel's authenticated Inertia group:

  | Prefix | Name prefix | Endpoints |
  | --- | --- | --- |
  | `settings/shipping/zones` | `panel.settings.shipping.zones.` | `index`, `store`, `edit`, `update`, `destroy`, `rates.store`, `rates.update`, `rates.destroy` |
  | `settings/shipping/methods` | `panel.settings.shipping.methods.` | `index`, `store`, `edit`, `update`, `destroy` |
  | `settings/shipping/exclusion-lists` | `panel.settings.shipping.exclusion-lists.` | `index`, `store`, `edit`, `update`, `destroy`, `products.search` |

- **Table extensions** for `shipping.zones.index`, `shipping.methods.index` and
  `shipping.exclusion-lists.index`, each contributing first-party Edit/Delete row actions
  through the public `TableExtension` seam so another add-on can anchor
  `Position::after('edit')`.
- **`discountTypeForms()`** returns `[ShippingDiscount::class => ShippingDiscountForm::class]`.
- **`langNamespaces()`** returns `['shipping']`; **`vite()`** returns the module config
  (`input: resources/js/panel.ts`, `buildDirectory: vendor/lunar-panel/shipping`,
  `__buildSourcePath: …/build`), which gives the package the `shipping-panel-assets`
  publish tag and `lunar:panel:link` support for free.

Settings pages do not use edit drafts ([[0051-panel-edit-drafts]]) — none of the existing
settings sections do — so these do not either.

### Screens

Every page renders through `SettingsShell`, imports only from `@lunarphp/panel`, and is
registered as `shipping::settings/shipping/...` in `panel.ts`.

**Zones index.** `DataTable` with name, type (badge), rates count, exclusion-lists count.
"Add zone" opens a `Dialog` with name + type (the two fields Filament's `CreateAction`
takes); success redirects to the edit page.

**Zone edit.** One `useForm` submitted to `update`, mirroring the tax-zone page:

- *Details* — name, type (`unrestricted | countries | states | postcodes`).
- *Coverage*, switched on type. `countries`: `Combobox` (with flags) adding to a chip list.
  `states`: a country `Combobox` narrowing a state `Combobox`, chips of selected states; the
  chosen country is synced as the zone's single country, as the Filament form does.
  `postcodes`: a country `Combobox` plus a `PostcodeListInput` (one per line, spaces
  stripped on the server, a helper explaining the resolver's wildcard support).
  `unrestricted`: an informational callout only.
- *Exclusion lists* — chips of attached lists with a `Combobox` to attach; saved with the
  form as `exclusion_lists`.
- *Rates* — a table of the zone's rates (method, status, base price in the default
  currency, tier count) with enable/disable and delete row actions and an "Add rate"
  button. Add/edit opens `RateSlideout`, posting to the dedicated `rates.*` endpoints
  because a rate carries per-currency base prices and a tier list and does not belong in
  the zone's own form. The slideout has: method `Select`; a base price input per enabled
  currency (default currency required); `RateTiersEditor`, a repeater of tiers whose
  threshold column flips between "Minimum spend" (major units, scaled per currency) and
  "Minimum weight" (in the method's `weight_unit`) according to the selected method's
  `data.charge_by`; an enabled `Toggle`. The tax notice keyed off
  `lunar.pricing.stored_inclusive_of_tax` sits above the prices, as in Filament.

**Methods index.** Name, code, driver, charge basis, an availability summary ("3 of 4
groups"). "Add method" `Dialog`: name, code (auto-slugged from name like a channel handle,
editable), driver.

**Method edit.**

- *Details* — name, code (unique), driver, description. Description is HTML today
  (Filament `RichEditor`); the panel's `RichTextEditor` is added to `ui.ts` so the add-on
  can use it (see Public surface).
- *Driver settings* — `DriverSettings` renders a block keyed on the driver: `ship-by` gets
  `charge_by` (`cart_total | weight`); `free-shipping` gets minimum spend per currency and
  the "use discount amount" toggle (the keys `FreeShipping` already reads from `data`);
  `flat-rate` and `pickup` have none. The driver list comes from
  `Shipping::getSupportedDrivers()`, so a consumer-registered driver appears with its key
  as the label and no settings block — its `data` is left untouched. Filament offers only
  `ship-by` and `pickup`; offering the whole registry is a deliberate parity-plus and the
  one place this spec goes beyond the Filament baseline.
- *Constraints* — weight unit (from `Converter::getMeasurements()['weight']`, "No weight
  restriction" default), min/max weight, `stock_available` toggle.
- *Schedule* — `ScheduleGrid`: a row per ISO weekday with an enabled checkbox and from/to
  time inputs, stored at `data.schedule` in the shape `ShippingMethod::isAvailable()`
  reads. This replaces both the unused `getAvailabilityScheduleComponent()` and the
  Livewire widget with a plain block on the edit page.
- *Customer groups* — `CustomerGroupAvailability`: a row per group with enabled and
  visible toggles and optional start/end `DatePicker`s, mapping to the pivot columns.

**Exclusion lists index.** Name, product count. "Add list" `Dialog` with name.

**Exclusion list edit.** Name, plus the excluded products as a `TargetChipList` fed by a
`TargetPickerDialog` — both already exported for third-party discount forms — backed by
the add-on's `products.search` endpoint (name, thumbnail, SKUs; excludes what the list
already holds). Saved with the form as `products`. Exclusions are stored as a
`purchasable` morph; the panel writes `Product` rows only, matching the Filament
`MorphToSelect`, but reads and displays any morph type it finds.

### `ShippingDiscount` in the panel

`Panel/DiscountTypeForms/ShippingDiscountForm implements Lunar\Panel\Contracts\DiscountTypeForm`,
the slice [[0072-panel-discounts-section]] deferred:

- `component()` → `shipping::ShippingDiscountForm`; `targetBuckets()` → `[]` (the type
  never touches lines).
- `toForm()` / `toStorage()` walk `data.methods[]`, scaling each rule's `prices` through
  `PriceCalculator` and the currency's `decimal_places`; `rules()` validate
  `methods.*.shipping_method_id` (nullable, exists), `methods.*.type` (`fixed |
  percentage`), `methods.*.percentage` (0 to 100 when percentage), `methods.*.prices.*`
  (numeric, min 0).
- `summary()` — "Free shipping" when every fixed rule is zero, "10% off shipping" for a
  single percentage rule, "Shipping from 2.00 GBP" (formatted through `PriceValue`) otherwise; null for mixed rule sets.
- The Vue form is a repeater of rules: method (`Any method` or a specific one), type,
  percentage or per-currency prices. The fixed-type inputs are labelled as the **resulting
  shipping price**, not an amount off — `ShippingDiscount::apply()` sets the breakdown
  item's price to the stored value rather than subtracting it, and the Filament label
  ("prices") leaves that ambiguous.
- `ShippingDiscount::getName()` reads `shipping::discounts.shipping_discount.name`, so the
  type name stops rendering as a raw key in the panel's discounts list.

### Frontend packaging

- `packages/table-rate-shipping/package.json` — `@lunarphp/table-rate-shipping-panel`,
  private, `build` / `dev` / `type-check` scripts, dev dependencies on
  `@lunarphp/panel`, `@lunarphp/panel-vite-plugin`, `vue`, `@inertiajs/vue3`, `vite`,
  `@vitejs/plugin-vue`, `vue-tsc`. Added to the root `workspaces` so it resolves
  `@lunarphp/panel` locally during monorepo development, as the example add-on does.
- `vite.config.js` uses `lunarPanelPlugin({ name: 'LunarShippingPanel' })`, input
  `resources/js/panel.ts`, `outDir: build`.
- **The compiled bundle is committed.** Unlike the example add-on, this package is
  installed through Composer, so the split repo must carry `build/`. The
  `build_panel_assets` workflow gains a second job that builds the shipping bundle on
  pushes to `2.x` touching `packages/table-rate-shipping/resources/js/**` and commits
  `packages/table-rate-shipping/build` back, force-added, in the same way it commits
  `packages/panel/public/build`.
- The CI `panel-js` job builds the add-on (`npm run build --workspace
  @lunarphp/table-rate-shipping-panel`) and type-checks it with `vue-tsc`.

### Public surface additions to the panel

Three components move onto `ui.ts` (and `@lunarphp/panel`'s `index.js`) because the
shipping screens need them and any add-on with settings pages, prose or country coverage
will too:

- `Section` — the titled card every settings edit page is built from.
- `RichTextEditor` — for the method description.
- `Flag` — country flags in coverage chips; `Combobox` already accepts a `flag` option
  field, but the chip list beside it has no way to render one.

`AvailabilityCard` is **not** exported: it is built around channels *and* customer groups
with the catalog's `values` map, while a shipping method has customer groups only.
`CustomerGroupAvailability` in the add-on is the narrower component.

### Translations

- The lang namespace is renamed `lunarpanel.shipping` → `shipping` across the package
  (Filament classes included), with the publish path becoming `lang/vendor/shipping`.
- New groups per locale: `nav.php`, `zones.php`, `methods.php`, `exclusion_lists.php`.
  The existing Filament groups keep their keys. `discounts.php` is shared by both admins.
- `de` and `nl` directories are added with every group, bringing the package to 16.
  English first; every other locale translated, never mirrored.

### Testing

- **Pest, `shipping` suite** — `tests/shipping/Feature/Panel/` on a new
  `Lunar\Tests\Shipping\PanelTestCase` that adds `InertiaServiceProvider`,
  `PanelServiceProvider` and the package's `resources/js/pages` path to
  `inertia.pages.paths` (the `ExampleAddonTestCase` pattern). Covers: the settings nav
  group is present for `shipping:manage` and absent without it; every index/edit renders
  the expected `shipping::…` component (with `shouldExist: false`) and props; create,
  update and delete for each entity; coverage switching clears the other coverage kinds;
  rate save scales money per currency including a zero-decimal currency and stores weight
  tiers raw; the products search excludes already-listed products; 403 on every route
  without the permission; `registeredVites()` carries the `shipping` module. Unit tests
  for every action in `tests/shipping/Unit/Actions/`, and for `ShippingDiscountForm`'s
  round trip, rules and `summary()`.
- **Pest, `admin` suite** — `DiscountForms::for()` resolves an implementing type
  unchanged and a mapped type through the registry; `ShippingDiscountForm` (Filament)
  contributes its section to `DiscountForm`.
- **Pest, `shipping` suite, unit** — the permission migration seeds `shipping:manage`
  against the core guard with the admin absent.
- **npm** — the new `ui.ts` exports are mirrored by hand into `@lunarphp/panel`'s
  `index.js` (nothing tests that sync today); `check-npm-drift` then fails until the
  package version is bumped, which is the intended reminder.
- **JS** — `vue-tsc` type-check and the Vite build in CI. Component-level vitest for
  `RateTiersEditor`, `ScheduleGrid` and `PostcodeListInput` runs through a small vitest
  config in the package reusing the panel's `happy-dom` setup.

## Alternatives considered

- **Keep `lunarphp/admin` as a hard requirement and add `lunarphp/panel` beside it.**
  Rejected — it forces Filament onto panel-only stores and, per [[0049-inertia-panel]],
  the panel depends only on core. Both admins optional is the only shape that matches the
  install model.
- **Leave `ShippingDiscount` implementing the Filament interface.** Rejected: the class
  cannot be autoloaded without `lunarphp/admin`, which makes "optional" a lie for anyone
  with the discount type registered. The bridge-side class map is the same seam the panel
  already uses and keeps every existing `instanceof` type working.
- **A `SectionExtension` grafting onto an existing settings section** instead of a
  `Section`. Rejected — shipping has its own routes, tables and nav group; there is no
  first-party section to extend.
- **Putting Shipping under the main sidebar** rather than Settings. Rejected: zones and
  methods are configuration edited rarely, like tax zones and locations, not a daily
  operational area like Orders.
- **Rates inside the zone form** as nested rows, the way tax rates are. Rejected: a tax
  rate is a name plus percentages; a shipping rate is per-currency base prices plus a tier
  list with a threshold that changes meaning per method. A slideout with its own endpoints
  matches Filament's slide-over and keeps the zone form validatable.
- **Reusing the existing `lunarpanel.shipping` lang namespace.** Not possible — vue-i18n
  splits on the dot.
- **Editing the schedule through a separate page or widget** as Filament does. Rejected;
  the panel's convention is one edit page with sections.
- **Exposing only `ship-by` and `pickup` as drivers** to match Filament exactly. Rejected
  in favour of the registry, since the manager already registers four drivers and the
  data keys `free-shipping` needs already exist. Flagged as a judgement call under Open
  questions.
- **Do nothing** — panel stores keep the raw JSON discount form and no shipping screens.
  That leaves the panel unable to run the only shipping add-on Lunar ships.

## Migration impact

- **Database**: no schema changes. Migration `…000013` is edited in place to resolve the
  guard through core (permitted while v2 is alpha).
- **Public contract surface**:
  - `ShippingDiscount` no longer implements `LunarPanelDiscountInterface`; its
    `lunarPanel*` methods move to `Filament/DiscountForms/ShippingDiscountForm`. No known
    external caller; no Rector rule.
  - `lunarphp/admin` and `filament/filament` become suggestions. A store that relied on
    the transitive requirement must require them itself.
  - Lang namespace `lunarpanel.shipping` → `shipping`; published overrides move from
    `lang/vendor/lunarpanel.shipping` to `lang/vendor/shipping`.
  - Three factories move from `Lunar\Shipping\Factories` to
    `Lunar\Shipping\Database\Factories`.
  - New: `Lunar\Shipping\Contracts\Actions\*` (eleven contracts) and
    `Lunar\Shipping\Panel\ShippingSection`.
  - Filament bridge: `LunarFilament::discountForm()` and `Support\DiscountForms` are
    additive.
  - Panel: `Section`, `RichTextEditor` and `Flag` join `ui.ts` (additive; bump `@lunarphp/panel`).
- **Upgrade path**: v1.x had no panel and the same Filament resources; nothing for the
  `upgrade` package.
- **Translations**: four new groups and two new locales in the package; the namespace
  rename touches every existing key.
- **Filament / admin**: the shipping resources keep working unchanged apart from the lang
  namespace and the extracted discount form. They are not moved onto the new actions.
- **Host app**: nothing to configure — the section registers itself when the panel is
  installed. Assets publish with `panel-all-assets` or `shipping-panel-assets`, or link
  with `lunar:panel:link`.
- **npm**: one new private workspace; no new third-party dependencies.

## Decisions taken during implementation

- **The bridge resolver lives on the existing extensions registry.** Rather than a new
  `Support\DiscountForms` class, `LunarFilament::discountForm()` / `discountForms()` /
  `discountFormFor()` sit on `ComponentExtensions\Registry`, which the bridge already
  binds and fronts with the facade. `discountFormFor()` returns the type itself when it
  implements the contract, so every existing type is untouched.
- **`Section` is exported to add-ons as well as `Flag` and `RichTextEditor`.** The
  settings edit pages are built from the panel's `Section` card, and an add-on settings
  page has no other way to match that layout. Three exports, not two.
- **The panel's `useForm` reserves `data`.** Inertia v3 rejects `data` as a form field
  name, so the method forms carry the driver block as `driver_data` (edit) or a flat
  `charge_by` (create) and rename it back under `data` in `transform()` on submit.
- **The schedule sits behind a toggle.** A stored schedule with every day disabled makes
  a method never available, which is what the Filament widget let staff save by
  accident. The panel stores `schedule: null` (always available) until the toggle is on,
  and `UpdateShippingMethod` treats a null data key as removal.
- **The discount type form gets its method list from a shared prop.** A `DiscountTypeForm`
  has no endpoint of its own and the form must not require `shipping:manage`, so the
  provider shares `shippingMethods` through Inertia for `panel.discounts.*` routes only.
  A `props()` hook on the contract would be the cleaner seam; it is a breaking change
  for every implementer and belongs to its own spec.
- **A rate's tiers submit with their editing form, not inline in the zone.** Confirmed as
  specced; enable/disable on the rates table re-submits the rate's own payload with the
  flag flipped rather than adding a fourth endpoint.
- **Add-on vitest was dropped.** The add-on imports `@lunarphp/panel`, which resolves to
  the runtime global; a vitest setup would need an alias into the panel's source tree
  and its dependency tree. The add-on is type-checked with `vue-tsc` and built in CI;
  behaviour is covered by the Pest feature tests against the real panel harness.
- **Rate deletion and per-zone scoping.** The `rates.*` routes 404 when the rate does not
  belong to the zone in the URL, checked in the controller rather than with scoped
  bindings, since the rate table has no route key on the zone.
- **The pre-existing English placeholders in eleven locales' `discounts.php` were
  translated** while adding the panel keys, as the locale rule requires no placeholder
  values in shipped lang files.
- **Vocabulary.** This branch was cut before [[0077-rename-collection-fulfilment-to-pickup]]
  merged, so the code still reads the `collection` driver key from the registry and its
  lang key. Nothing here hardcodes the key; the rename lands independently.

## Open questions

- **Driver exposure** _(judgement)_ — offer all four registered drivers, or mirror
  Filament's two? Proposed: all four, since the data keys already exist. Owner: Glenn,
  before `accepted`.
- **Where `ShippingDiscount` ultimately lives** — [[0072-panel-discounts-section]] left
  open whether a core free-shipping type replaces it. This spec ships the panel form
  where the type is today; if the type moves to core, the form moves with it and this
  spec's slice 6 becomes the starting point rather than wasted work.
- **Should the Filament resources adopt the new actions** in a follow-up so both admins
  share one write path? Proposed: yes, as a small separate spec covering every Filament
  resource, not just shipping.

## References

- Feature-parity baseline: `packages/table-rate-shipping/src/Filament/Resources/*` and
  `src/Filament/Widgets/AvailabilityScheduleWidget.php`.
- First-party template: `packages/panel/src/Sections/Settings/TaxZonesSection.php`,
  `Http/Controllers/Settings/TaxZone{Index,Create,Edit}Controller.php`,
  `Http/Requests/Settings/TaxZoneRequest.php`, `resources/js/pages/settings/tax-zones/*`,
  and core `Actions/TaxZones/UpdateTaxZone.php`.
- Add-on template: `packages/panel-addon-example/` (README, `ExampleSection`,
  `resources/js/addon.ts`, `vite.config.js`), `tests/panel/Fixtures/ExampleAddonTestCase.php`,
  `tests/panel/Feature/ExampleAddonTest.php`.
- Discount seam: `packages/panel/src/Contracts/DiscountTypeForm.php`,
  `Support/DiscountTypeForms/FixedAmountOffForm.php`, `Support/DiscountTypeSchema.php`;
  Filament `packages/filament/src/Schemas/Discount/DiscountForm.php`,
  `packages/admin/src/Filament/Resources/DiscountResource/Pages/EditDiscount.php`.
- Permission plumbing: `packages/core/src/Auth/Manifest.php`,
  `packages/core/database/state/EnsureBaseRolesAndPermissions.php`,
  `PanelServiceProvider::registerPermissionGate()`.
- Asset shipping: `.github/workflows/build_panel_assets.yml`, `.github/workflows/tests.yml`
  (`panel-js` job), `scripts/check-npm-drift.mjs`.
- [[0049-inertia-panel]] — extension model. [[0072-panel-discounts-section]] — the deferred
  `ShippingDiscountForm` slice. [[0051-panel-edit-drafts]] — why settings pages skip drafts.
  [[0077-rename-collection-fulfilment-to-pickup]] — the `pickup` driver vocabulary used here.

## Implementation plan

- [x] Slice 1 — Decouple the package: core-only `require`, bridge `discountForm()` map +
      `DiscountForms` resolver, Filament `ShippingDiscountForm` extracted from the type,
      permission migration on the core guard, lang namespace rename, `de` / `nl` locales,
      factory namespaces, `ShippingExclusion::list()` fix, tests.
- [x] Slice 2 — Actions and contracts for zones, methods, rates and exclusion lists, bound
      in `ShippingServiceProvider`, with unit tests.
- [x] Slice 3 — Panel scaffold + Zones: `ShippingSection`, nav group, routes, zone
      index/create/edit with coverage and exclusion-list attachment, `RateSlideout` and
      the `rates.*` endpoints, `panel.ts`, npm workspace, Vite config, CI build +
      type-check, `build_panel_assets` job, `PanelTestCase`, feature tests.
- [x] Slice 4 — Methods: index/create/edit with `DriverSettings`, constraints,
      `ScheduleGrid`, `CustomerGroupAvailability`; `RichTextEditor` export; tests.
- [x] Slice 5 — Exclusion lists: index/create/edit, `products.search`, chip list and
      picker; `Flag` export; tests.
- [x] Slice 6 — `ShippingDiscountForm` (panel) + `ShippingDiscountForm.vue`,
      `discountTypeForms()` registration, `getName()` repointed; closes
      [[0072-panel-discounts-section]] slice 6; tests.
