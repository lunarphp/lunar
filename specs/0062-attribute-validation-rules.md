# 0062 — Per-attribute validation rules

- Status: accepted
- Author: Glenn
- Created: 2026-08-25
- TODO item: Per-attribute validation rules — restore the v1 feature across both panels

## Problem

Lunar 1.x let staff attach custom Laravel validation rules to an attribute. `lunar_attributes` carried a nullable `validation_rules` string (pipe-delimited, e.g. `min:1|max:10`), the admin attribute form exposed it as a text input, and every admin field-type converter applied it to its Filament component via `->rules($attribute->validation_rules)`.

v2 dropped the feature but left its debris:

- The v2 baseline (`packages/core/database/migrations/2026_01_01_000018_create_attributes_table.php`) has no `validation_rules` column, and the upgrade migration (`packages/upgrade/database/migrations/2026_06_01_000003_convert_attribute_data_keys_to_ids.php:333`) **drops the v1 column outright, discarding rules merchants configured in 1.x**.
- The nine Filament bridge converters (`packages/filament/src/FieldTypes/*`) still read `$attribute->validation_rules` at 10 call sites. The read is always empty — and under `Model::preventAccessingMissingAttributes()` / `Model::shouldBeStrict()` it throws `MissingAttributeException`, breaking every attribute-backed edit form (issue #2568).
- The `attribute.form.validation_rules.*` lang keys (label + helper) still ship in all 16 locales of both `lunarphp/admin` and `lunarphp/filament`, referencing a form field that no longer exists.
- The Inertia panel has no trace of the feature: `AttributeSchema::rules()` (`packages/panel/src/Support/AttributeSchema.php:168-197`) derives rules only from the field type and `required`, plus a single configuration-derived `max:{max_items}` for lists.

Upgrading merchants lose both their data and a capability they rely on. PR #2597 proposed deleting the dead reads (option 1 of the issue); this spec takes option 2 — reinstate the feature properly.

## Proposal

Restore per-attribute validation rules as a first-class attribute property, stored as a JSON list of Laravel rule strings and enforced by both admin UIs.

### Core

- Add `$table->json('validation_rules')->nullable()` to the baseline `create_attributes_table` migration, after `required` (alpha policy: fold into the baseline, no change migration).
- Cast `validation_rules` to `array` on `Lunar\Core\Models\Attribute`; docblock `@property ?array $validation_rules`.
- Shape: a list of rule strings — `["min:1", "max:10"]`. The array form supersedes v1's pipe string so rules containing pipes (`regex:…|…`) survive intact.
- Factory: default `null`; add a state for tests that need rules.
- Core stores and exposes the rules; it does not enforce them. Enforcement stays a concern of the editing surface, as in v1.

### Filament bridge (`lunarphp/filament`)

- The nine field-type converters replace the dead read with the real one: `->rules($attribute->validation_rules ?? [])`. The column now exists, so strict mode is satisfied — this resolves #2568 at the root.
- `RelationManagers/AttributeGroup/AttributesRelationManager` gains a `TagsInput::make('validation_rules')` beside the `required` toggle, reusing the existing `lunar-filament::attribute.form.validation_rules.*` keys. Update the English helper to the array phrasing ("One rule per tag, e.g. min:1, max:10"), mirror across the other 15 locales.

### Inertia panel (`lunarphp/panel`)

- `AttributeSchema::rules()` appends the attribute's custom rules to the type-derived set. Rules describe a single stored value: scalar types append on the field key; `translated_text` and `list` append on the `.*` per-value entry. Every draft resource (`ProductDraftResource`, `ProductTypeDraftResource`, `CollectionDraftResource`, `BrandDraftResource`, `VariantFields`) picks this up for free via `DraftManager`.
- `AttributeRequest` accepts the field: `'validation_rules' => ['nullable', 'array']`, `'validation_rules.*' => ['string', 'max:255']`, and `attributeAttributes()` passes it through to persistence.
- The attribute edit page (`resources/js/pages/settings/attributes/Edit.vue`) gains a tags-style input in the Behaviour section bound to `form.validation_rules`. New `validation_rules_label` / `validation_rules_helper` keys in `packages/panel/resources/lang/{locale}/attributes_settings.php`, all 16 locales.
- `AttributeFields.vue` needs no change — errors already flow from the draft commit 422 into `errors[field.key]`.

### Upgrade package

- `convert_attribute_data_keys_to_ids` stops dropping `validation_rules`. Before the schema reshape it converts the v1 string into the v2 list: split on `|`, trim, drop empties — exactly how Laravel's validator parses string rules, so the conversion is lossless relative to v1 semantics — then re-states the column as json.

### Adjacent fix: validator attribute labels

Custom rules make validation errors far more visible, and today `DraftManager` (`packages/panel/src/Drafts/DraftManager.php:88-90`) passes no attribute names to `Validator::make`, so messages read "The attribute:hero_cta field is required." `AttributeSchema::labels()` already produces the right map; pass it as the validator's custom attribute names, and merge attribute labels into `ProductDraftResource::labels()` (the one resource that omits them).

### Trust model

Rules are authored by staff and evaluated by Laravel's validator — the same trust model as v1. Database-touching rules (`exists:`, `unique:`) can reference arbitrary tables; that is accepted for staff-facing configuration and documented in the helper text.

## Alternatives considered

- **Store under the `configuration` JSON via a universal `getConfigurationFields()` descriptor.** No schema change, and both generic configuration renderers would pick it up automatically. Rejected: validation is an attribute-level concern like `required`, not per-type configuration; descriptors are declared per field type, so a universal key needs every subclass — and every consumer-registered field type — to merge a parent descriptor; the panel's configuration section is edit-only and absent from the create dialog.
- **Remove the dead reads and drop the feature (PR #2597).** Fixes the strict-mode crash but abandons a v1 capability and silently discards upgrading merchants' data.
- **A structured rule-builder UI (rule pickers, per-rule inputs).** Over-engineering relative to v1 parity; free-form rule strings are what upgrading users know. A builder can layer on later without changing storage.

## Migration impact

- Database: baseline `create_attributes_table` gains a nullable json column (alpha policy — no change migration). Upgrade migration converts instead of drops.
- Public contract: net-additive — new column/cast, new form fields, converter reads change from a missing attribute to a real one. No Rector rule needed.
- Translations: helper-text rewording in `admin` + `filament` `attribute.php` (16 locales each); two new keys in panel `attributes_settings.php` (16 locales).
- Filament/admin: attribute form gains one input; attribute-backed edit forms stop crashing under strict mode.

## Open questions

- Should core actions enforce the rules when `attribute_data` is written outside the panels (storefront/API)? v1 did not; parity says no. Revisit in the storefront API spec.
- ~~Should the attribute form validate the rule strings themselves?~~ Resolved: yes. `Lunar\Core\Rules\ValidRuleString` dry-runs each entry against a probe validator, so unknown rules, missing parameters, and malformed patterns are rejected while the rule is being authored instead of throwing a 500 when a record using the attribute is saved. Wired into the panel's `AttributeRequest` and the Filament attribute form's `TagsInput` (nested recursive rules).

## References

- Issue #2568 — strict-mode crash and the option 1 / option 2 fork.
- PR #2597 — option 1 implementation (superseded by this spec; its strict-mode regression test transfers).
- v1 implementation: `origin/1.x` `packages/admin/src/Filament/Resources/AttributeGroupResource/RelationManagers/AttributesRelationManager.php:118-125`, `packages/admin/src/Support/FieldTypes/*`.
- Panel seams: `AttributeSchema::rules()` / `labels()`, `DraftManager::commit`, `AttributeRequest`, `settings/attributes/Edit.vue`.

## Implementation plan

- [x] Slice 1 — core: baseline column, cast, factory state; upgrade migration converts v1 data.
- [x] Slice 2 — filament: converters apply rules (strict-mode regression test included), attribute form input, lang updates.
- [x] Slice 3 — panel: `AttributeSchema::rules()`, `AttributeRequest`, Edit.vue input, lang keys, draft-commit tests.
- [x] Slice 4 — panel: validator attribute labels in `DraftManager` (`labelsForMorph()` covers products/variants, whose mapping needs no hydrated record) + `ProductDraftResource::labels()` / `VariantFields::labels()`.
- [x] Slice 5 — docs: filament README attribute section, filament CHANGELOG entry (the other touched packages keep no changelog).
