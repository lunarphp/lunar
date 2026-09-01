# 0061 — Product option types (Text / Colour / Swatch)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-22
- TODO item: Product option types

## Problem

Product options are typeless. A `ProductOption` carries a translatable `name`, a
`handle`, a `shared` flag, and an unused translatable `label`; each
`ProductOptionValue` carries a translatable `name` and a `position`. Every value
therefore renders as plain text everywhere it appears — the settings list, the
option edit screen, the product variant builder, and (by extension) any
storefront that reads the option set.

That is limiting in ways the prototype makes concrete
(`lunar-v2-ui/src/pages/settings/ProductOptionEdit.vue`,
`.../ProductOptionsList.vue`, `.../data/productOptions.js`):

- A "Colour" option cannot store the colour it represents, so the admin and
  storefront can only show the word "Navy", never a `#1F2A44` swatch.
- A "Material" or pattern option cannot carry a thumbnail image per value.
- The panel option-edit screen has no concept of a value beyond its name and a
  numeric position field; there is no drag-reorder, no per-value payload editor,
  no usage/activity context, and the DB `label` column is never surfaced.
- The settings list shows no indication of what an option *is* — no type, no
  preview of its values.

The prototype resolves this by giving every option a **type** that drives how its
values render and what payload each value carries:

- `text` — value name only (today's behaviour).
- `colour` — each value carries a hex colour.
- `swatch` — each value carries an image thumbnail.

It also designs for **forward compatibility**: an option whose type the admin
doesn't recognise (a future `gradient`, `numeric`, …) degrades gracefully rather
than breaking the screen.

The prototype's product-editing screens (variant builder, shared/exclusive value
pickers, variants table) render these previews too, but that alignment depends on
the type concept and per-value payloads existing first. This spec lands the
foundation; the product-editing alignment is the final slice.

## Proposal

### Data model

**Option type.** Add a `type` string column to `product_options`, defaulting to
`text`. Because v2 is still in alpha, fold this into the baseline migration
`2026_01_01_000012_create_product_options_table.php` rather than shipping a change
migration.

```php
$table->string('type')->default('text')->index()->after('handle');
```

A backing enum names the known cases and centralises presentation metadata:

```php
namespace Lunar\Core\Enums;

enum ProductOptionType: string
{
    case Text = 'text';
    case Colour = 'colour';
    case Swatch = 'swatch';
}
```

The model keeps `type` as a plain string cast (not an enum cast) so an unknown
value read from the database round-trips untouched; callers resolve it with
`ProductOptionType::tryFrom($option->type)` and treat `null` as "unknown, render
the fallback". This is what lets a future type sit in the data without the
current admin choking on it.

**Value payloads.** No new columns on `product_option_values`:

- `colour` — stored on the existing `meta` jsonb as `meta->colour` (a `#RRGGBB`
  string). Validated against `/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/`, stored
  upper-cased.
- `swatch` — stored as a single-image Spatie media collection `swatch` on
  `ProductOptionValue` (already implements `SpatieHasMedia` via `HasMedia`).
  Reuses the panel's existing media surface (`MediaManager` /
  `VariantMediaPicker`), not a bespoke uploader.

Keeping colour on `meta` and swatch on media avoids widening the values table for
a type-specific concern and matches how the rest of the model already stores
optional, shape-varying data.

**Label.** The `label` column already exists (translatable jsonb, "internal label
shown only in the admin"). No schema change; it is simply surfaced in the panel.

### Core

- `Enums/ProductOptionType` as above, with a `label()` translation-key helper and
  an `icon()` helper (`type`, `palette`, `image`) so the panel and any consumer
  share one source of truth.
- `ProductOption`: cast `type` (string), add `type` to the documented property
  block, add a `scopeType(Builder, string)` for the list filter.
- `CreateProductOption` / `UpdateProductOption`: accept `type` in the attribute
  array and persist it. Extend value sync so each value row may carry
  `colour` (written to `meta->colour`) and `swatch` (media add/replace/clear).
  Value rows still key on `id` so variant links survive.
- **Type change.** When `UpdateProductOption` sees the option's `type` change, it
  clears every value's per-type payload (`meta->colour` unset, `swatch` media
  cleared) while preserving each value's `name`, `position`, and variant links.
  This mirrors the prototype's "Existing per-type values will be cleared. Value
  names and positions are kept." confirmation. Expose it as a guarded path in the
  action rather than a separate endpoint.
- Factory states: `ProductOptionFactory::colour()` and `::swatch()` seeding
  representative values; `ProductOptionValueFactory` gains `colour()` and
  `swatch()` states. Demo-data updated so the seeded catalogue shows each type.

### Panel — settings edit screen

Rebuild `settings/product-options/Edit.vue` to the prototype
(`lunar-v2-ui/.../ProductOptionEdit.vue`), keeping the panel's `SettingsShell` /
`Section` scaffolding and extension seams:

- **Two-column layout.** Left: details + values. Right: a usage card (products
  count with a "View products" link filtered to the option), a timestamps card
  (created / updated), and an activity card (recent `LogsActivity` entries, "View
  all" link). These read-only cards degrade to empty states when there's nothing
  to show.
- **Details.** Name (translatable), Label (translatable, hint "Internal label
  shown only in the admin"), Handle (mono, with a "changing the handle may break
  references" warning shown only when the option is in use and the handle
  changed), and a Type row: a `StatusBadge` showing the current type + a "Change
  type" trigger.
- **Type change dialog.** A `ConfirmDialog` (danger tone) with a three-up type
  picker (`RadioCardGroup` / button grid), body copy matching the prototype, and
  the clearing semantics above. Only the known types are offered; if the current
  type is unknown it still displays via the fallback badge.
- **Values.** Drag-to-reorder list (replacing today's numeric position input),
  each row: grip, a `ValuePreviewChip`, the value name (translatable), a
  per-type editor column, a variant-count / "Unused" indicator, and a
  remove/lock control (locked with a tooltip when the value is carried by
  variants — reusing today's `inUse` guard). The per-type editor column renders:
  - `colour` → a `ColorPicker` (hex text + native `<input type="color">`).
  - `swatch` → the panel media picker bound to the value's `swatch` collection.
  - `text` → no editor column.
  - unknown → a muted "Unsupported type" note; the row stays editable for
    name/position so data is never stranded.

### Panel — settings list screen

Extend `settings/product-options/Index.vue` and its section/table classes:

- **Type column** rendering a `StatusBadge` (with the type's icon) via
  `ProductOptionsTableExtension`.
- **Value previews** — up to four `ValuePreviewChip`s plus a `+N` overflow chip
  per row, so colour/swatch options read at a glance.
- **Filters** — a type filter (driven by `scopeType`) and a "show unused only"
  toggle, wired through the existing add-on filter mechanism the table already
  exposes.
- **Create dialog** gains a Type picker (`RadioCardGroup`) alongside name +
  handle; the store endpoint accepts and persists `type`. A disabled "more coming
  soon" affordance is optional and can be dropped.

### Panel — product-editing alignment (dependent)

Once the above ships, bring the product editor closer to the prototype
(`lunar-v2-ui/src/components/ProductOptions.vue`, `OptionRow.vue`,
`ValuePreviewChip.vue`, `VariantsTable.vue`): the option builder's value tokens
and the variant matrix render colour/swatch previews instead of plain names,
using the same `ValuePreviewChip`. This is presentation-only — the builder's
shared/exclusive model and generate flow are unchanged. The broader
option-builder redesign in the prototype (drag, drift banner, etc.) is out of
scope here and, if wanted, gets its own spec.

### Shared component

Port `ValuePreviewChip.vue` from the prototype into the panel component set. It
takes `(type, value, size)` and renders a text pill, a colour square (with a
light-colour border-contrast rule), a swatch thumbnail, or the unknown-type
fallback pill. It is the single renderer used by the list, the edit screen, and
the product editor. Export it via `resources/js/ui.ts` so add-ons can reuse it.

### Requests / validation

Extend `ProductOptionRequest`:

- `type` — `required`, `string`, `Rule::in(text, colour, swatch)` on create;
  on update the same set (the change dialog only offers known types).
- `values.*.colour` — `nullable`, `string`, `regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/`,
  required-ish only when the option type is `colour` (soft — an empty colour is
  allowed and renders the neutral placeholder).
- Swatch media handled through the panel's existing media upload flow, not this
  request.

## Alternatives considered

- **Dedicated `colour` / value columns instead of `meta`.** Rejected: colour is
  type-specific and optional; `meta` already exists for exactly this, and a
  column would sit null for every non-colour value. If storefront querying by
  colour becomes a real need later, promoting it to a column is a clean,
  additive follow-up.
- **Storing the swatch as a `meta` URL** (as the prototype does for convenience).
  Rejected for the real implementation: values already support Spatie media, and
  reusing it gives conversions, validation, and the existing panel media UX for
  free.
- **An enum cast on `type`.** Rejected: an enum cast throws on an unknown stored
  value, defeating the forward-compatibility the prototype deliberately designs
  for. A string cast plus `tryFrom` at the edges keeps unknown types inert.
- **A separate "change type" endpoint.** Rejected: it is a normal update with a
  clearing side effect; folding it into `UpdateProductOption` keeps one write
  path and one set of authorisation/validation rules.
- **Do nothing.** Leaves options text-only and blocks the product-editing
  redesign that depends on this.

## Migration impact

- **Database:** one baseline migration edit — add `type` (string, default
  `text`, indexed) to `product_options`. No change to `product_option_values`
  (colour on `meta`, swatch on media). Existing rows default to `text`, so the
  change is non-breaking.
- **Public contract:** additive. New `Lunar\Core\Enums\ProductOptionType`; new
  `type` attribute and `scopeType` on `ProductOption`; `CreateProductOption` /
  `UpdateProductOption` accept new optional keys. No signatures removed.
- **Upgrade path (v1.x):** v1 product options have no type; the upgrade sets
  `type = 'text'` (the column default already handles rows created before the
  key is written). No Rector rule needed for consumer code unless a downstream
  app constructs option attributes positionally (it doesn't — options are
  array-filled). Note it in the `upgrade` package's product-option transform.
- **Translations (16 locales):** new keys for type labels (`type_text`,
  `type_colour`, `type_swatch`, plus an unknown fallback), the type-change dialog
  (title/body/confirm/new-type label), the label field + hint, the usage /
  timestamps / activity cards, value previews, and the "unsupported type" note.
  English first, then mirrored across `ar, bg, de, es, fa, fr, hr, hu, mn, nl,
  pl, pt_BR, ro, tr, vi`.
- **Filament / admin:** the legacy Filament admin also manages product options.
  The `text` default keeps it working unchanged; adding a type field there is
  optional and out of scope for this spec (the Inertia panel is the target
  surface). Call out explicitly in the PR that Filament is left on `text`.
- **Search:** `ProductOptionIndexer` may optionally index `type`; not required
  for this spec.

## Open questions

Resolved before implementation (all per the recommendations):

- **Per-locale value name editing** — resolved: adopt the panel's `TranslatedInput`
  for name, label, and value names. The request now accepts locale-keyed maps and
  merges them over stored translations.
- **Unknown-type reachability** — resolved: ship the fallback as defensive
  (no v2 create path produces an unknown type). `type` is a plain string cast
  resolved with `tryFrom`; a type registry is deferred to a future spec.
- **Storefront exposure** — resolved: not needed for the panel work and deferred.
  Storefront/API exposure of `type` + colour/swatch is a follow-up when the
  storefront consumes it; the data (column + `meta->colour` + media) is in place.

## References

- Prototype: `lunar-v2-ui/src/pages/settings/ProductOptionEdit.vue`,
  `src/pages/settings/ProductOptionsList.vue`, `src/data/productOptions.js`,
  `src/components/{ValuePreviewChip,ColorPicker,OptionRow,ProductOptions,VariantsTable}.vue`.
- Current panel: `packages/panel/resources/js/pages/settings/product-options/{Index,Edit}.vue`,
  `src/Http/Requests/Settings/ProductOptionRequest.php`,
  `src/Http/Controllers/Settings/ProductOptionEditController.php`,
  `src/Sections/Settings/{ProductOptionsSection,Tables/*}.php`.
- Core: `packages/core/src/Models/{ProductOption,ProductOptionValue}.php`,
  `src/Actions/ProductOptions/*`, migrations `…_000012_…` and `…_000030_…`.
- Related specs: [[0056-panel-product-types-section]],
  [[0057-panel-products-section]], [[0060-panel-media-groups]].

## Implementation plan

- [x] Slice 1 — Core: `ProductOptionType` enum, `type` column + attribute
      default + cast + `scopeType`, value colour (`meta`) and swatch (media)
      persistence in `UpdateProductOption`, type-change clearing, factory states,
      tests. (Demo-data has no product-option fixtures to enrich.)
- [x] Slice 2 — Panel settings edit: two-column layout, label + type badge +
      change-type dialog, drag-reorder values (`useDragSort`), per-type value
      editors (`ColorPicker` / `SwatchInput` with per-value media endpoints),
      usage / timestamps / activity cards, `ValuePreviewChip`; request accepts
      translation maps + colour, swatch upload/delete routes; translations.
- [x] Slice 3 — Panel settings list: type column, value previews, type +
      unused filters, create-with-type dialog; translations.
- [x] Slice 4 — Product-editing alignment: colour/swatch previews in the option
      builder value pills and the variants table via `ValuePreviewChip`
      (presentation only; builder selection model unchanged).

Slices land behind the type column's `text` default, so each is independently
shippable. `ValuePreviewChip` and `ColorPicker` are exported from `ui.ts` for
add-ons. Verified: `pest` core (1172) + panel (736) suites, `vitest` (241),
`vue-tsc`, `phpstan`, `pint`.
