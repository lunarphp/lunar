# 0054 — Declarative field-type configuration schema

- Status: accepted
- Author: Glenn (with Claude)
- Created: 2026-07-20
- TODO item: Panel settings — attribute field-type configuration

## Problem

Attribute field types carry per-type configuration (`attributes.configuration` JSON), but only
the Filament admin can edit all of it, and only through Filament-specific code:

- `Lunar\Filament\FieldTypes\*::getConfigurationFields()` returns **Filament form components**
  per type. The full set today: Text/TranslatedText → `richtext` toggle; Number → `min`/`max`
  numeric inputs; Dropdown → `lookups` key/value editor; File → `file_types` tags input (mime
  suggestions), `multiple` toggle, `min_files`/`max_files`, `disk` select, `directory` text.
  List, Toggle, Vimeo and YouTube declare none (List's `max_items` exists in core's validation
  contract but has no Filament editor).
- The panel's attribute Edit page hardcodes a config section per built-in type (dropdown
  lookups, richtext toggle, number min/max) in `settings/attributes/Edit.vue`. List's
  `max_items` is not editable at all, and File's settings don't exist in the panel.
- A consumer-registered field type (`FieldTypeManifest::add()`) can declare validation via
  `FieldType::getConfig()`, but has **no way to surface configuration inputs** in the panel —
  the Vue frontend cannot render arbitrary consumer components.
- The core contract `FieldType::getConfigurationFields()` ("describe the configuration fields
  used to build the field type's config UI") exists but returns `[]` everywhere — the seam was
  reserved and never implemented.

## Proposal

Make core `FieldType::getConfigurationFields()` the canonical, renderer-agnostic description
of a field type's settings, and have **both admins** build their configuration forms from it:
the panel renders descriptors directly; the Filament bridge maps them to Filament components.
A field type — built-in or consumer-registered — declares its settings once, in core.

### Descriptor shape (core)

`getConfigurationFields()` returns a list of arrays:

```php
/** @return array<int, array{key: string, type: string, label: string, hint?: string, suggestions?: array<int, string>, options?: array<int, array{label: string, value: string}>}> */
public function getConfigurationFields(): array;
```

- `key` — the `configuration` array key the input binds to (`min`, `richtext`, `lookups`, …).
- `type` — one of a small vocabulary of panel-renderable controls:
  `text`, `number`, `toggle`, `select` (with `options`), `tags` (list of strings, optional
  `suggestions`), `lookups` (the dropdown label/value rows editor).
- `label` / `hint` — display strings, translated server-side (`__('lunarpanel-core::…')` for
  built-ins; consumers use their own lang namespaces).

Built-in implementations:

| Field type | Descriptors |
| --- | --- |
| Text, TranslatedText | `richtext` (toggle) |
| Number | `min` (number), `max` (number) |
| Dropdown | `lookups` (lookups) |
| ListField | `max_items` (number) |
| File | `file_types` (tags, mime suggestions), `multiple` (toggle), `min_files` (number), `max_files` (number), `disk` (select over `filesystems.disks`), `directory` (text) |
| Toggle, Vimeo, YouTube | none |

Labels ship in a new core lang group (16 locales) so descriptors are translated wherever
they're consumed.

### Validation (panel)

`AttributeRequest` derives `configuration.*` rules from the attribute's field type via
`getConfig()['options']`, replacing the hardcoded dropdown-lookups rules added while fixing
the lookups shape bug. Core rule strings get corrected to be nullable where the value is
optional (`min` is currently `numeric|min:1`, which both rejects blank values and forbids
legitimate minima below 1 → becomes `nullable|numeric`; `richtext` → `nullable|boolean`).

### Panel rendering

`AttributeEditController` serves `configFields` (the descriptors for the attribute's type);
`settings/attributes/Edit.vue` replaces its hardcoded per-type sections with a generic
renderer switching on descriptor `type`. The existing lookups rows editor becomes the
`lookups` control. Unknown descriptor types render nothing (forward compatibility).

A consumer-registered field type then gets a working settings UI in the panel by implementing
`getConfigurationFields()` + `getConfig()` — no frontend code required.

### Filament consumption

The bridge maps core descriptors to Filament components (text → `TextInput`, number →
`TextInput::numeric`, toggle → `Toggle`, select → `Select`, tags → `TagsInput` with
suggestions, lookups → `KeyValue` carrying the existing map↔rows hydrate/dehydrate mutators)
via `Lunar\Filament\Support\Forms\ConfigurationFieldMapper`. The mapping hooks in at
`AttributeData::getConfigurationFields($type)` rather than on `BaseFieldType` itself — the
static method has no type context, and `AttributeData` is its only consumer; this also means a
core-registered custom type gets a Filament config form with **no bridge class at all**.
`BaseFieldType::getConfigurationFields()` stays as the override hook: a non-empty return wins
over the descriptor mapping. The per-type bridge implementations (TextField, Number, Dropdown,
TranslatedText, File) delete their bespoke component lists. Core's lang group becomes the
canonical label source; the bridge's `fieldtypes.*.form.*` keys stay only for overrides and
deprecate over a minor cycle.

## Alternatives considered

- **Panel-side registry of Vue components per field type** — lets add-ons ship custom config
  UIs via the panel add-on runtime. Rejected for now: heavier contract, and the descriptor
  vocabulary covers every setting the Filament admin has today. An add-on escape hatch can
  layer on later without breaking descriptors.
- **Reuse `getConfig()` validation rules to infer inputs** — rules don't carry labels, control
  kinds, or options; inference would be fragile (e.g. `richtext` is `nullable`, not
  `boolean`, today).
- **Do nothing** — custom field types stay read-only-configured in the panel, and List's
  `max_items` remains uneditable everywhere.

## Migration impact

- Database: none (`configuration` JSON unchanged).
- Public contract: `FieldType::getConfigurationFields()` changes meaning from "unused, returns
  []" to the descriptor list above — technically breaking for any consumer already overriding
  it (unlikely; it has never been consumed). Rector rule not warranted; release notes entry.
- Core `getConfig()` rule fixes (`min`) relax validation — non-breaking.
- Translations: new core lang group across 16 locales; panel `attributes_settings` keys for
  the generic section stay. Bridge `fieldtypes.*.form.*` keys deprecate (kept one minor cycle).
- Filament/admin: behavioural shift on the bridge's public surface — `BaseFieldType::
  getConfigurationFields()` becomes descriptor-mapping instead of empty, and per-type bespoke
  implementations are removed. Rendered forms stay equivalent (same controls, same keys), and
  the static method remains overridable, so downstream panels see no functional change unless
  they called the removed per-type methods expecting hardcoded lists — release notes entry,
  no Rector rule needed.

## Open questions

- Should the File type's settings render in the panel while file *values* are still
  Filament-only? (Proposed: yes — configuration and value editing are independent.)
- Does `disk` belong in a `select` sourced server-side at request time (proposed) or should
  descriptors support lazy option resolvers?

## References

- [[0019-attribute-system-redesign]] — field-type enum/class mapping, configuration column.
- [[0049-inertia-panel]] — deferred dynamic attribute/field-type rendering.
- [[0052-panel-brands-section]] — attribute schema renderer the panel uses on edit pages.

## Implementation plan

- [x] Slice 1 — core: descriptor implementations on built-in field types + `fieldtypes` lang group (16 locales) + `getConfig()` rule fixes.
- [x] Slice 2 — panel: serve `configFields`, generic config renderer in attribute Edit page, generic `configuration.*` validation from `getConfig()`; drop hardcoded sections.
- [x] Slice 3 — filament: `ConfigurationFieldMapper` + `AttributeData` descriptor fallback, remove per-type bespoke configuration fields, README section.
- [x] Slice 4 — tests: descriptor unit tests (core), panel feature tests incl. a custom registered field type end-to-end, filament mapper tests.
