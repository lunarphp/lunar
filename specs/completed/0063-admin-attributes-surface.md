# 0063 — Standalone attributes surface in the Filament admin

- Status: implemented
- Author: Glenn
- Created: 2026-08-25
- TODO item: Standalone attributes surface in the Filament admin

## Problem

The Filament admin's only route to an attribute is `AttributeGroupResource` -> `AttributesRelationManager`. Every create, edit, and delete happens inside a group's edit page.

v2 made `attribute_group_id` nullable, and the Inertia panel treats groups as optional: its Settings -> Attributes screen lists every attribute, grouped or not. That leaves the Filament admin with three concrete failures:

- **Ungrouped attributes are unreachable.** An attribute with no group appears in no relation manager, so it can never be edited or deleted from the Filament admin. A store whose attributes were created in the panel (or migrated from v1 with the group made nullable) can have its entire attribute set invisible there.
- **The two admins fight over `attribute_group_id`.** The panel's attribute form always submits the group id; saving an attribute there with no group selected detaches it from its group — silently undoing an assignment made in Filament, and removing it from the only Filament surface that could show it.
- **Grouping is compulsory at creation.** The relation manager's CreateAction always creates into its owner group, so the Filament admin cannot produce the ungrouped attributes v2's schema explicitly allows.

## Proposal

Give the Filament admin a first-class attributes surface mirroring the panel's Settings -> Attributes screen, built from bridge schema classes per the `{Model}Form` / `{Model}Table` convention.

### Bridge (`lunarphp/filament`)

- Extract the form the relation manager builds inline into `Schemas\Attribute\AttributeForm` with the standard granular helpers (`getNameComponent()`, `getHandleComponent()`, `getModelTypesComponent()`, `getFlagsComponent()`, `getValidationRulesComponent()`, `getTypeComponent()`, `getConfigurationComponent()`), plus a `getGroupComponent()` — a nullable `Select` on `attribute_group_id`.
- Extract the inline table into `Tables\Attribute\AttributeTable`: name, handle, type, group (badge, empty state for ungrouped), plus a group filter that includes an "ungrouped" option.
- `AttributesRelationManager` keeps its shape but delegates to `AttributeForm` / `AttributeTable`, hiding the group component (the owner record supplies it). Its create/edit `using()` callbacks move into shared support so the resource and the relation manager persist identically (model_types sync, configuration mutation, position assignment).

### Admin shell (`lunarphp/admin`)

- New `AttributeResource` (List / Create / Edit pages) composing the bridge classes, registered in the settings navigation group next to Attribute Groups.
- Delete follows the panel's rule: system attributes cannot be deleted.

### Panel (`lunarphp/panel`)

- No behavioural change required by this spec. The panel's always-submitting group select stops being destructive once Filament can see ungrouped attributes, because nothing becomes unreachable — the second failure above is downgraded from data loss to an ordinary edit.

### Translations

- New keys for the resource labels, the group field, the group filter, and the ungrouped state under `lunar-filament::attribute.*` — English first, mirrored across the other 15 locales.

## Alternatives considered

- **Make `attribute_group_id` required again.** Regresses the v2 schema decision and breaks the panel, which already manages ungrouped attributes.
- **Leave attribute management to the panel only.** The Filament admin is a turnkey product; shipping it unable to reach part of the attribute set is not acceptable, and downstream panels composing the bridge would inherit the same hole.
- **Bolt a "no group" pseudo-row onto the group list.** Cheaper, but leaves attribute management two clicks deep inside a different resource and does not fix compulsory grouping at creation.

## Migration impact

- Database: none.
- Public contract: net-additive — new `AttributeForm` / `AttributeTable` bridge classes and a new admin resource. The relation manager's public behaviour is unchanged; its inline schema moving into `AttributeForm` is internal-to-contract since the manager itself keeps working.
- Translations: new keys across all 16 locales in `lunar-filament`.
- Upgrade: no Rector rule needed.

## Open questions (resolved)

- Should `AttributeResource` opt into global search alongside the five existing descriptors? **No** — attributes are staff-configuration rather than commerce records.
- Should the attribute group edit page's relation manager allow *attaching* an existing ungrouped attribute (AttachAction) as well as creating new ones? **Deferred** — still leaning yes, tracked in the bridge's IDEAS backlog; the standalone resource already covers regrouping via the edit form's group select.

## References

- Spec 0062 — per-attribute validation rules ([[0062-attribute-validation-rules]], PR #2627): the change that made this gap block testing; its `TagsInput` lives in the shared form this spec extracts.
- `packages/panel` Settings -> Attributes (`AttributeIndexController`, `AttributeEditController`) — the surface being mirrored.
- `packages/filament/src/RelationManagers/AttributeGroup/AttributesRelationManager.php` — the inline form/table to extract.

## Implementation plan

- [x] Slice 1 — bridge: extract `Schemas\Attribute\AttributeForm` + `Tables\Attribute\AttributeTable`, shared persistence support, relation manager delegates.
- [x] Slice 2 — admin: `AttributeResource` with List/Create/Edit pages, navigation registration, delete guard for system attributes.
- [x] Slice 3 — lang keys (16 locales), filament README, tests (resource pages, ungrouped visibility, group filter, relation manager parity).

Persistence note: the shared support turned out to already exist — the core `CreatesAttribute` / `UpdatesAttribute` / `DeletesAttribute` actions the panel uses. The bridge gained `Actions\Attributes\{Create,Edit,Delete}AttributeAction` + `DeleteAttributesBulkAction` wrappers delegating to them, so both admins persist through one code path. Two behavioural consequences: a relation-manager create now takes the global `max(position) + 1` (previously the group's row count + 1), and the relation manager now enforces the system-attribute delete rule it previously lacked.
