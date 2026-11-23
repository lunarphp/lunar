# Attribute descriptions

- Status: proposed
- Author: Glenn
- Created: 2026-11-23
- TODO item: Attribute descriptions — restore the v1 helper text shown beside attribute fields, in core, both admin UIs, and the upgrade

## Problem

Lunar 1.x let staff give an attribute a description. `lunar_attributes` carried a nullable translatable `description` column (jsonb), the v1 admin attribute form exposed it as a `TranslatedText` input ("Use to display the helper text below the entry"), and every v1 field-type converter passed it to its Filament component as `->helperText($attribute->translate('description'))`. Merchants used it to tell editors what an attribute is for, what format a value takes, or where a value comes from.

v2 dropped the feature without a replacement, and a community member has reported it missing. Its removal was not a decision against it: spec 0019 lists the jsonb `description` among the columns it drops, with no reason, alongside `validation_rules`, which spec 0062 has since restored. What is left today:

- **Core.** The baseline `packages/core/database/migrations/2026_01_01_000018_create_attributes_table.php` and `Lunar\Core\Models\Attribute` have no `description`.
- **Upgrade.** `convert_attribute_data_keys_to_ids` (`packages/upgrade/database/migrations/2026_06_01_000003_convert_attribute_data_keys_to_ids.php`, `reshapeAttributesSchema()`) drops only `attribute_type`, `section` and `default_value`. An upgraded store keeps its v1 jsonb `description` column and the text merchants wrote, but nothing in v2 reads it, and a fresh v2 install has no such column. The two kinds of install have different `attributes` schemas.
- **Filament bridge.** The nine converters in `packages/filament/src/FieldTypes/*` still call `->helperText(null)` (ten call sites), and `YouTube` / `Vimeo` call `->helperText(null ?? __('lunar-filament::components.forms.youtube.helperText'))` — the v1 read with the description replaced by `null`. The `attribute.form.description.*` and `attribute.table.description.*` lang keys still ship in all 16 locales of `lunarphp/admin` and `lunarphp/filament`, for a form field that no longer exists.
- **Inertia panel.** `AttributeSchema::field()` (`packages/panel/src/Support/AttributeSchema.php`) serialises `key`, `handle`, `label`, `required`, `type` and `config`, with no description. `AttributeFields.vue` renders a label, the input and the error for each field, and accepts only one `description` for the whole section. The attribute edit page has hints about the attribute's own settings, but no field where staff can write text for editors.

## Proposal

Restore the description as an optional, plain-string attribute property. Core stores it; both admin UIs let staff edit it and show it under the attribute's input as helper text.

### Core

- Add `$table->string('description')->nullable()` to the baseline `create_attributes_table` migration, after `name` (alpha policy: fold into the baseline, no change migration).
- `Lunar\Core\Models\Attribute` docblock gains `@property ?string $description`. No cast. The model's `$guarded = []` already lets `CreateAttribute` / `UpdateAttribute` persist it.
- `AttributeFactory` defaults it to `null`.
- The description is a plain string, like `name` since spec 0019 flattened it. See Alternatives for the translated shape.
- Core only stores it. Nothing in core reads or renders it, and it is not indexed for search.

### Inertia panel (`lunarphp/panel`)

- `AttributeRequest` accepts `'description' => ['sometimes', 'nullable', 'string', 'max:255']`, and `attributeAttributes()` passes it through when present, so an update without the key leaves the stored value untouched (the same pattern as `validation_rules`).
- `AttributeEditController::edit()` adds `description` to the `attribute` prop.
- `settings/attributes/Edit.vue` gains a full-width `Textarea` under the name and handle in the Details section, bound to `form.description`, with a `FieldLabel` and a hint explaining that editors see the text under the field. The create dialog on `Index.vue` stays as it is: staff add a description from the edit page, which the create dialog redirects to.
- `AttributeSchema::field()` adds `'description' => $attribute->description` to each serialised field. Every surface built on it — `ProductDraftResource`, `ProductTypeDraftResource`, `CollectionDraftResource`, `BrandDraftResource`, `VariantFields`, the customer and customer group pages — picks it up without further change.
- `AttributeFields.vue`: the `AttributeField` interface gains `description?: string | null`, and each field renders `<div v-if="field.description" class="mt-1 text-[11px] text-ink-500">{{ field.description }}</div>` directly under its input and above the error line, the helper-text style the attribute edit page already uses. Text renders as text, never as HTML.
- New keys in `packages/panel/resources/lang/{locale}/attributes_settings.php`, all 16 locales: `field_description` ("Description") and `description_hint` ("Shown under the field as helper text").

### Filament bridge (`lunarphp/filament`)

- The converters replace `->helperText(null)` with `->helperText($attribute->description)`, and `YouTube` / `Vimeo` with `->helperText($attribute->description ?? __('lunar-filament::components.forms.youtube.helperText'))`, restoring the v1 fallback. `TextField` has two call sites (rich text and plain); both change.
- `Lunar\Filament\Schemas\Attribute\AttributeForm` gains `getDescriptionComponent()`, a `Textarea::make('description')` with `->maxLength(255)`, labelled and helped by the existing `lunar-filament::attribute.form.description.*` keys, listed in `getMainComponents()` after the name. The keys already exist in all 16 locales, so the bridge needs no translation changes.
- `lunarphp/admin` builds its attribute screens from the bridge and needs no change of its own.

### Upgrade package

Add a guarded data migration, `2026_06_01_000023_add_attribute_description`, that brings any database to the v2 shape:

- No `description` column (an alpha install, or a v1 database that never had one): add `string('description')->nullable()`.
- A JSON-typed `description` column (a v1 database, whether or not it has already run `convert_attribute_data_keys_to_ids`): flatten each row's translations into one string the same way `flattenJsonbNameColumns()` flattens `name` — the `en` value when present, otherwise the first locale — then change the column to `string` nullable. A value longer than 255 characters is truncated to 255, and a row with no usable text becomes `null`.
- A string `description` column: nothing to do, so re-runs are no-ops.

One-way, no `down()`, like the other upgrade steps. It runs after `convert_attribute_data_keys_to_ids`, which keeps its current behaviour.

### Docs

- `docs/reference/attributes.mdx`: a `description` row in the fields table (`string` `nullable`, "Helper text the admin panels show under the attribute's field").
- `docs/getting-started/overview/alpha-upgrade-guide.mdx`: a row under "Next release" — `attributes` | Add `description` `string` `nullable`. | `2026_06_01_000023_add_attribute_description`.
- `docs/getting-started/overview/upgrade-guide.mdx`: a line under the data migrations noting that v1 attribute descriptions carry over, flattened to the default locale.

## Alternatives considered

- **A translated description (JSON map per locale), as in v1.** Keeps every locale v1 merchants wrote. Rejected for now: the text is for staff, not shoppers; spec 0019 flattened the attribute's own `name` to a plain string on the same reasoning, and a translated description beside a single-language name would be inconsistent. It would also need `TranslatedInput` on the settings page and a locale choice when rendering. If staff-facing attribute text becomes translatable, `name` and `description` should change together in their own spec.
- **Store it under `configuration`.** No schema change. Rejected for the same reason spec 0062 rejected it for validation rules: the description is an attribute-level property, not field-type configuration, and every field type, including consumer-registered ones, would have to declare it.
- **Reuse the `FieldLabel` `hint` slot.** It renders right-aligned beside the label in small text, sized for a few words. Helper text is often a sentence, and v1 showed it under the input, so it gets its own line.
- **Do nothing.** Upgraded stores keep an unused column full of merchant-written text, fresh installs differ in schema, and editors lose guidance they had in v1.

## Migration impact

- Database: the baseline `create_attributes_table` gains a nullable string column (alpha policy, no change migration). One new guarded upgrade migration adds or flattens the column.
- Public contract: additive — a new column and model property, a new `AttributeForm` component method, a new key in the panel's serialised attribute field. No Rector rule needed.
- Upgrade path for v1: descriptions survive, flattened to the `en` (or first) locale. Other locales are dropped, as they are for `name`.
- Translations: two new keys in panel `attributes_settings.php`, in all 16 locales and translated in each. The `admin` and `filament` description keys already exist and are reused.
- Filament / admin: the attribute form gains one input, and attribute-backed edit forms show helper text again.

## Open questions

- Should 255 characters be enough? v1 had no limit on the jsonb column. Owner: Glenn, before the spec is accepted. If longer text is needed, the column becomes `text` and the request and form limits rise to match; nothing else changes.
- Should the storefront or a future storefront API expose the description? Out of scope here; it stays a staff-facing property until the storefront API spec decides.

## References

- Community report that v2 attribute descriptions are missing.
- v1 implementation: `origin/1.x` `packages/admin/src/Filament/Resources/AttributeGroupResource/RelationManagers/AttributesRelationManager.php:57-62`, `packages/admin/src/Support/FieldTypes/*` (`->helperText($attribute->translate('description'))`).
- [[0019-attribute-system-redesign]] — dropped the column.
- [[0062-attribute-validation-rules]] — restored another property 0019 dropped, across the same surfaces; this spec follows its shape.
- Panel seams: `AttributeSchema::field()`, `AttributeRequest`, `AttributeEditController`, `settings/attributes/Edit.vue`, `components/AttributeFields.vue`.
- Upgrade seam: `convert_attribute_data_keys_to_ids::flattenJsonbNameColumns()` / `flattenName()`.

## Implementation plan

- [ ] Slice 1 — core and upgrade: baseline column, model docblock, factory default; `add_attribute_description` upgrade migration with tests for a v1 jsonb column (with and without `en`), an over-long value, a missing column and a re-run; alpha upgrade guide row.
- [ ] Slice 2 — panel: `AttributeRequest`, `AttributeEditController`, `Edit.vue` input, `AttributeSchema::field()`, `AttributeFields.vue` helper text with a vitest case, lang keys in 16 locales, feature tests for saving and serialising.
- [ ] Slice 3 — filament: converters read the description, `AttributeForm` description component, a test that a converter's component carries the helper text.
- [ ] Slice 4 — docs: attributes reference and upgrade guide.
