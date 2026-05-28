# 0019 — Attribute system redesign

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-05-27
- TODO item: "Rework the custom attribute system"

> **Delivery (decided):** single branch, committed in core-first order, considered done only when the whole monorepo is green (full Pest suite + Pint + PHPStan across every `packages/*`). A core-only green PR is not achievable because PHPStan/Pest run monorepo-wide and the Filament/admin packages query the `attribute_type` / `attributable_type` columns and `mappedAttributes` relationship this redesign changes.
> **Commit order:** (1) core — schema, cast, cache, FieldType base/enum/manifest, models, observer + purge job, `HasAttributeData` concern, factories, core tests; (2) Filament/admin retarget — field-type renderers, Livewire synthesizers, `Forms/Components/Attributes`, `AttributeSelector`, `AttributeGroupForm`/`AttributeGroupTable`, `AttributeGroupResource` + relation manager, the collection create-actions and `RecordSearch`/`CreatesChildCollections`, admin `EditProductVariant`; (3) `packages/upgrade` Rector rules + v1→v2 data mapper.
> There is no `packages/demo-data`; seeder updates in §F do not apply.

## Problem

The attribute system carried over from v1 stores `attribute_data` as a per-key envelope keyed by **handle**, wrapping the field type alongside the value:

```json
{
  "product_name": { "field_type": "Lunar\\Core\\FieldTypes\\Text", "value": "Widget" },
  "active":       { "field_type": "Lunar\\Core\\FieldTypes\\Toggle", "value": true }
}
```

Concrete problems in `packages/core/src/` today:

- **The field type is duplicated on every row.** `Casts/AsAttributeData::set` writes `get_class($item)` into each entry, even though the field type belongs on the `Attribute` definition, not on every model's data. The JSON is bloated and the stored class name is a hard dependency — renaming or moving a FieldType class orphans existing data (`Casts/AsAttributeData::get` silently `continue`s past any `field_type` that no longer `class_exists`).
- **Data is keyed by handle, so renaming a handle breaks reads.** Stored keys are attribute handles; there is no link back to a stable identifier. Renaming an attribute's handle silently disconnects every model from its data.
- **`Models/Attribute` is morph-shaped and over-coupled to grouping.** The `attributes` table has an `attribute_type` morph column and a **required** `attribute_group_id` FK (`->constrained(...)`, not nullable). An attribute cannot be ungrouped, and which model types it applies to is encoded as a single morph string rather than a many-to-many. `Models/AttributeGroup` similarly carries an `attributable_type` morph.
- **No caching of the handle ⇄ id ⇄ field-type lookup.** Any cast that resolves handles to field types would hit the DB per model row.
- **FieldTypes have no shared base and no canonical type string.** Each class under `FieldTypes/` reimplements `$value`, `jsonSerialize()`, `__toString()`, `getValue()`/`setValue()`. There is no enum mapping the `attributes.type` string (`text`, `toggle`, …) to its class — `FieldTypes\Manifest` hardcodes a class list, and the `type` column is a free string with no source of truth.
- **Deleting an attribute leaves data behind.** `Models\Attribute::booted` deletes `attributables` pivot rows on delete, but nothing strips the attribute's data out of every attributable model's `attribute_data` JSON.

Net effect: storage is bloated and fragile, the attribute model conflates "which models use me" and "which group am I in" into morph columns, and the field-type layer has no shared base or registry the way the rest of the package (spec 0013, spec 0016) now expects.

## Proposal

Store raw values keyed by stable `Attribute.id` on disk; hydrate to handle-keyed FieldType instances in memory. Replace the morph columns with a plain nullable group FK plus an `attribute_models` join table. Give FieldTypes a shared base, a backing enum, and a registry that follows the spec 0013 / 0016 conventions.

### A. Storage format

`attribute_data` stores **raw scalar/array values keyed by the Attribute's numeric id**:

```json
{ "1": "Widget", "2": true, "3": 42 }
```

- Keys are stable `Attribute.id` values — handles rename freely without touching stored data.
- Values are raw — no field-type metadata. The field type is resolved from the `Attribute` definition at hydration time.
- Deleting an attribute orphans its id key, cleaned up by a purge job (§G).

After the cast, the in-memory representation is a `Collection` keyed by **handle**, each value a FieldType instance:

```php
$product->attribute_data;   // Collection ['name' => Text('Widget'), 'active' => Toggle(true), ...]
$product->attr('name');     // 'Widget'  — raw value via the FieldType's getValue()
```

**ID-keyed raw values on disk, handle-keyed FieldType instances in memory.**

### B. Database schema

Schema changes ship as a new migration; the flat baseline (spec 0003) is not edited.

#### `attribute_groups`

| Column     | Type   | Notes                          |
|------------|--------|--------------------------------|
| id         | bigint | PK                             |
| name       | string |                                |
| handle     | string | unique, auto-slugified         |
| position   | int    | default 1                      |
| system     | bool   | default false (locks deletion) |
| timestamps |        |                                |

Drops the current `attributable_type` morph column and the `jsonb` `name`.

#### `attributes`

| Column             | Type        | Notes                                          |
|--------------------|-------------|------------------------------------------------|
| id                 | bigint      | PK                                             |
| attribute_group_id | bigint null | FK → `attribute_groups`, **nullOnDelete**      |
| name               | string      |                                                |
| handle             | string      | unique, auto-slugified                         |
| type               | string      | a `FieldTypeEnum` value, e.g. `text`, `toggle` |
| configuration      | json null   | field-type-specific config, cast to collection |
| position           | int         | default 1                                      |
| required           | bool        | default false                                  |
| searchable         | bool        | default false                                  |
| filterable         | bool        | default false                                  |
| system             | bool        | default false                                  |
| timestamps         |             |                                                |

Drops `attribute_type` (morph), `section`, `default_value`, `validation_rules`, and the `jsonb` shape of `name`/`description`. Makes `attribute_group_id` nullable and `nullOnDelete`.

#### `attribute_models` — which model *types* an attribute applies to

| Column       | Type   | Notes                                       |
|--------------|--------|---------------------------------------------|
| id           | bigint | PK                                          |
| attribute_id | bigint | FK → `attributes`, cascadeOnDelete          |
| model_type   | string | morph alias, e.g. `product`, `collection`   |

Unique on `[attribute_id, model_type]`. Replaces the `attribute_type` morph column as the way an attribute declares which model **types** it applies to. An attribute can now apply to more than one model type. The `model_type` stores the morph **alias** (`product`), consistent with the package morphMap, not the FQCN.

#### `product_type_attribute` — which attributes a ProductType exposes (decided)

| Column          | Type   | Notes                                  |
|-----------------|--------|----------------------------------------|
| id              | bigint | PK                                     |
| product_type_id | bigint | FK → `product_types`, cascadeOnDelete  |
| attribute_id    | bigint | FK → `attributes`, cascadeOnDelete     |
| timestamps      |        |                                        |

Unique on `[product_type_id, attribute_id]`. This is the **rename of the existing `attributables` pivot**, scoped to its only real use: `ProductType` declaring which `product`/`variant` attributes its products and variants expose (today's `ProductType::mappedAttributes()` / `productAttributes()` / `variantAttributes()`, and the Filament `AttributeSelector`). `attribute_models` answers "what model type does this attribute apply to"; `product_type_attribute` answers "which attributes does *this* product type present" — they are different relationships and both are needed. Product/variant split is derived by intersecting with `attribute_models` (`product` vs `variant`), so `productAttributes()`/`variantAttributes()` filter the pivot by the attribute's model type.

> The current `attributables` morph pivot (`attributable_type`, `attributable_id`, `attribute_id`) was only ever exercised by `ProductType`. It is **narrowed**, not dropped: replaced by the typed `product_type_attribute` pivot above. No general per-instance attribute pivot is introduced — the JSON column is the per-instance store.

#### `attribute_data` column

`json('attribute_data')->nullable()` already exists on `products`, `product_variants`, `brands`, `collections`, `customers`, `customer_groups`. No column change — only the stored shape changes (§F migration).

### C. Models

Register `Attribute`, `AttributeGroup`, and `AttributeModel` in the package morphMap in `bootingPackage()`. All extend `Models\Base` and implement a `Contracts\` interface (existing convention).

**`AttributeGroup`**
- Casts: `system` → bool.
- `setHandleAttribute()` slugifies the handle.
- `attributes(): HasMany` ordered by `position`.

**`Attribute`**
- Casts: `configuration` → collection; `system`, `searchable`, `filterable`, `required` → bool.
- `setHandleAttribute()` slugifies the handle.
- `group(): BelongsTo` → `AttributeGroup` (nullable FK; an attribute may be ungrouped).
- `models(): HasMany` → `AttributeModel`.
- `fieldType(): FieldType` — instantiates the FieldType class for `$this->type`, resolved via `FieldTypeManifest`.
- On group delete, attributes are **nulled, not deleted** (DB `nullOnDelete`).

**`AttributeModel`**
- Wraps an `attribute_models` row (`attribute_id`, `model_type`).

**`ProductType`** (unchanged behaviour, retargeted relationship)
- `mappedAttributes(): BelongsToMany` → `Attribute` through `product_type_attribute` (replaces the morphToMany through `attributables`).
- `productAttributes()` / `variantAttributes()` filter `mappedAttributes()` by the attribute's model type (`product` / `variant`) via `attribute_models`, instead of the old `whereAttributeType(...)` on the morph column.

The current `Models\Attribute::booted` `attributables`-deletion hook is removed; the `product_type_attribute` rows are cleaned by the FK `cascadeOnDelete`, and `attribute_data` cleanup moves to the observer (§G).

### D. The cast — `Casts/AsAttributeData`

Rewrite the existing cast to do the id ⇄ handle and raw ⇄ FieldType translation, using `AttributeCache` (§E) for the lookups so it does not hit the DB per row.

**get()** — DB → memory:
1. Decode the JSON (`{"1": "Widget", ...}`).
2. For each `id => rawValue`: look up the handle and FieldType class via the cache; skip ids the cache no longer knows (orphaned, pending purge).
3. Instantiate the FieldType with the raw value.
4. Return a `Collection` keyed by handle.

**set()** — memory → DB:
1. Accept a `Collection` of FieldType instances (keyed by handle or id).
2. Resolve each key to its Attribute id via the cache.
3. Call `jsonSerialize()` on each FieldType to get the raw value.
4. Encode to JSON keyed by id string.

### E. `AttributeCache`

Caches the handle ⇄ id ⇄ field-type maps forever; flushed by the observer (§G) on any attribute change. Holds two maps:

```php
[
  'by_handle' => ['name' => 1, 'active' => 2, ...],
  'by_id'     => [
      1 => ['handle' => 'name',   'field_type_class' => Text::class],
      2 => ['handle' => 'active', 'field_type_class' => Toggle::class],
  ],
]
```

Methods: `getIdForHandle($handle)`, `getHandleForId($id)`, `getFieldTypeClassForId($id)`, `flush()`.

Per spec 0016, the cache is a service-layer class: bound to a `Contracts\` interface in `LunarServiceProvider`, with collaborators injected. The cast resolves it from the container rather than `new`-ing it. It lives in a new `core/src/Cache/` folder (approved as a base-folder addition for this spec; spec 0013's folder list is extended to include `Cache/` — "container-resolved cache services").

### F. FieldType layer (`FieldTypes/`)

**Contract — `Contracts\FieldType` (extends `JsonSerializable`).** Move the existing `FieldTypes\FieldType` interface into `Contracts\` (spec 0013: every interface lives in `Contracts/`, no `Interface` suffix). Methods:

```php
public function getValue(): mixed;
public function setValue(mixed $value): void;
public function getConfig(): array;
public function getConfigurationFields(): array; // describes the config UI
```

**`AbstractFieldType`.** New base class holding `$value`; `jsonSerialize()` returns the raw value (what lands in the DB); `__toString()`. Existing field types (`Text`, `Number`, `TranslatedText`, `Toggle`, `Dropdown`, `ListField`, `File`, `Vimeo`, `YouTube`) extend it and stop reimplementing the boilerplate.

**`FieldTypeEnum` (string-backed)** under `Enums/` — maps type string → class:
`text → Text`, `number → Number`, `translated_text → TranslatedText`, `toggle → Toggle`, `dropdown → Dropdown`, `list → ListField`, `file → File`, `vimeo → Vimeo`, `youtube → YouTube`.

Each type validates its value on `setValue` and serializes to a raw scalar/array:

| Type | Value | Config |
|------|-------|--------|
| Text | string\|null | |
| Number | int\|float\|null | |
| Toggle | bool | |
| TranslatedText | `locale => text` collection | |
| Dropdown | string\|null | `options` |
| ListField | array | |
| File | string\|array\|null | `file_types`, `multiple`, `max`, `min` |
| Vimeo / YouTube | embed id | |

**`Manifests\FieldTypeManifest`.** Relocate/rename the existing `FieldTypes\Manifest` to `Manifests\FieldTypeManifest` (alongside `Manifests\AttributeManifest`, per spec 0013). Seed the registry from `FieldTypeEnum`. Methods: `add($type, $class)`, `remove($type)`, `getType($type)`, `getTypes()`. Register additions in `bootingPackage()`, never `registeringPackage()`. The `Contracts\FieldTypeManifest` interface gains `remove`/`getType` and keys by type string rather than only listing classes.

### G. Observer + purge

**`Observers\AttributeObserver`** (spec 0016 service-layer conventions):
- on `saved`/`updated` → `AttributeCache::flush()`.
- on `deleted` → flush the cache **and** dispatch a `PurgeAttributeData` job that strips the deleted attribute's id key out of every attributable model's `attribute_data` JSON (chunked per attributable table).

### H. Concerns

**`Models\Concerns\HasAttributeData`** replaces the current `HasAttributes` trait:
- `initializeHasAttributeData()` registers the `AsAttributeData` cast for the `attribute_data` key, so each model does not repeat the cast.
- `attr(string $handle): mixed` → `attribute_data[$handle]?->getValue()`.

Add this trait to every attributable model (`Product`, `ProductVariant`, `Brand`, `Collection`, `Customer`). The old morph-relationship trait (`mappedAttributes()` via `attribute_type`) is removed.

## Alternatives considered

- **Keep the handle-keyed envelope, add a migration on rename.** Rejected: every handle rename would have to rewrite every attributable row, and the field-type-per-row bloat remains. ID keys make renames free.
- **Store the FieldType class string on each row but key by id.** Rejected: still duplicates definition data and couples stored rows to class names. The field type belongs on the `Attribute` definition and is cheap to resolve via the cache.
- **Keep the `attribute_type` morph column instead of `attribute_models`.** Rejected: an attribute can legitimately apply to more than one model type, and the morph column forces a single owner. The join table also lets the group FK become a plain nullable relationship.
- **Do nothing.** Rejected: the envelope format is a known v1 wart and the morph/required-group coupling blocks ungrouped and multi-model attributes.

## Migration impact

- **Baseline migrations edited in place (stage 1).** v2 is pre-release; the flat baseline is still being shaped, and recent schema specs (0017 `list_price`, 0018 dedicated name fields) edit the `2026_01_01_*` baseline migrations directly rather than adding new ones. This spec follows that: reshape `2026_01_01_000001_create_attribute_groups_table` (drop `attributable_type` + jsonb name, add `name` string + `system`), reshape `2026_01_01_000018_create_attributes_table` (drop `attribute_type`, `section`, `default_value`, `validation_rules`; nullable `nullOnDelete` group FK; `name` string; `type`; `configuration` json; `searchable` default **false**), and repurpose `2026_01_01_000039_create_attributables_table` into `product_type_attribute` (typed FKs) **plus** create `attribute_models`. The `attribute_data` columns on the six attributable tables already exist and do not change shape at the DB level. (Once v2 ships, this rule flips back: post-release schema changes go in new migrations per CLAUDE.md.)
- **No core data migration.** Because v2 is unreleased there is no live data in the old shape to convert inside core; factories emit the new shape directly. The old-format conversion is a **stage-3 `packages/upgrade` migration**, not a core one.
- **Data migration (stage 3, upgrade package).** `..._convert_attribute_data_keys_to_ids`: for each attributable table, chunk rows; where `attribute_data` is in the old shape (handle keys and/or `{ field_type, value }` envelopes), map handle → attribute id, unwrap the value, rewrite as `{ "<id>": <rawValue> }`. Per the existing memory on upgrade migrations, this is one-way (no `down()`); restore from backup to reverse. ([[feedback-upgrade-migrations-no-down]])
- **Breaking changes to the public contract surface:**
  - `Casts\AsAttributeData` stored format changes (id-keyed raw vs handle-keyed envelope).
  - `FieldTypes\FieldType` interface moves to `Contracts\FieldType` and gains `getConfigurationFields()`; `setValue`/`getValue` gain explicit types.
  - `FieldTypes\Manifest` moves to `Manifests\FieldTypeManifest`; `Contracts\FieldTypeManifest` signature changes (keyed by type string, gains `remove`/`getType`).
  - `Models\Concerns\HasAttributes` is replaced by `HasAttributeData`; `mappedAttributes()` and the `attribute_type` morph relation are removed.
  - `Models\Attribute` loses `attributable()`/`scopeSystem($type)` morph behaviour; gains `group()`, `models()`, `fieldType()`.
  - These need Rector rules in the `upgrade` package and a spec callout (per the package's public-contract policy).
- **Upgrade path for v1.x consumers (stage 3).** The `packages/upgrade` v1 → v2 mappers must emit the new id-keyed raw format. Rector rules cover the renamed/moved classes above (existing `LunarSetList` entries at lines 46–47, 54, 71–73, 102, 387 are updated). There is no `packages/demo-data` to update.
- **Translation / locale impact.** Any new user-facing strings (e.g. field-type labels surfaced by `getConfigurationFields()`) must land in all 16 locales — English first, then mirrored. The redesign itself is mostly structural; audit for new keys during implementation.
- **Filament / admin impact.** The admin attribute resources read `attribute_data`, the field-type list, and the group relationship. Forms/tables that assume handle-keyed envelopes, the `attribute_type` morph, or a required group must move to the new cache-backed accessors, `FieldTypeEnum`, and the nullable group. Verify the attribute management UI end-to-end in the host app.

## Resolved decisions

- **`AttributeCache` folder placement** → new `core/src/Cache/` folder (approved). Spec 0013's folder list is extended to cover it.
- **`attribute_models.model_type`** → stores the morph **alias** (`product`), not the FQCN, consistent with the package morphMap.
- **ProductType-scoped attributes** → keep a dedicated pivot: `attributables` is narrowed/renamed to `product_type_attribute` (§B). `attribute_models` and `product_type_attribute` are distinct relationships and both ship in stage 1.
- **`searchable` default** → `false`. The data migration preserves existing per-row values; only the column default changes.
- **Scope / staging** → core-first in three stages (see header). No `packages/demo-data` exists, so §F seeder work is dropped.

## Open questions

- None blocking. Stage 2/3 details (exact Filament synthesizer changes, Rector rule list) are worked out when those stages start.

## Acceptance checks

Feature tests hitting the real DB for each:

- `$product->attribute_data` returns a `Collection` of FieldType instances keyed by handle.
- `$product->attr('handle')` returns the raw value.
- Saving an attribute-data collection persists `{"<id>": raw}` JSON (assert the actual DB column).
- Renaming an attribute handle does **not** change stored data; reads still resolve.
- Deleting an attribute flushes the cache and purges its id from all attributable rows.
- Deleting a group nulls `attribute_group_id` on its attributes (data intact).
- A custom field type registered via `FieldTypeManifest::add()` round-trips through the cast.

## References

- [[0013-base-directory-reorganisation]] — folder responsibilities (`Contracts/`, `Manifests/`, `Enums/`, `Casts/`, `Models/Concerns/`).
- [[0016-service-layer-di]] — cache/manifest/observer/job are service-layer classes: constructor injection, bound to `Contracts/` interfaces, no facades/`app()` mid-method.
- [[0018-dedicated-name-description-fields]] — dedicated columns sit alongside the custom `attribute_data` covered here.
- `packages/upgrade` — v1 → v2 mappers and Rector rules for the breaking changes above.
