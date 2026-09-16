# 0086 — Panel draft slices: namespaced contributions to first-party edit drafts

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-15
- TODO item: Panel draft slices — one contract for namespaced draft contributions, used by first-party surfaces and by add-on slot components alike (spec 0086)

## Problem

Two problems share a cause.

**Add-ons cannot take part in a save.** An add-on can put a component on a first-party
edit page through a slot zone, but `PanelSlot` hands it only the slot's static props plus
whatever the page binds to the zone. It cannot store values in the page's draft, so what
staff type into it never autosaves and is lost on navigation. It cannot commit alongside
the record, so its only option is a side request to its own endpoint, outside the commit
transaction and with ordering left to chance. It gets none of the conflict detection,
restore banner, dirty guard, or validation-error plumbing the draft layer gives
first-party fields. The pressure this creates is the request in
[lunar#2736](https://github.com/lunarphp/lunar/discussions/2736) for a field-level hook
into first-party forms, the Filament `extendForm` model. That is the wrong shape for the
panel: once an add-on can reach into a form it does not own, two add-ons installed
together can silently break each other. The panel's extension surface is additive by
design, and this spec keeps it that way.

**First-party sub-surfaces are hand-rolled.** `ProductDraftResource` already composes
several namespaced slices into one draft: attributes under `attribute:{handle}`,
availability rows under `channel:{id}` and `customer_group:{id}`, the simple-shape sole
variant under `variant:{field}`. Each has its own current values, normalisation, rules,
labels, and commit path, but they are wired by hand inside the resource with prefix
checks in every method. Adding another slice (associations, slugs, prices, media, see
[[0087-product-editing-through-the-draft]]) means more of the same, and none of it is
reusable by the brand, collection, or product-type resources that share the same
editing components.

Both need the same thing: a contract for a namespaced form contribution, and one
composer that assembles a resource from its slices.

### The stance on extending first-party forms

An add-on can add fields to any first-party form's save. Its fields live under a
namespace it owns, are validated and committed with the form's own, and can never
read, hide or alter the form's first-party fields. Which form (a drafted edit page, a
create page, a settings form) decides the plumbing, not the contract. This spec
delivers the drafted edit pages; [[0088-panel-form-slices]] extends the same contract
to the panel's plain forms.

## Proposal

A **draft slice** is a registered, namespaced contribution to a draftable resource. It
declares its own fields, current values, normalisation, rules, labels, and commit, all in
unprefixed terms; the panel composes it into the resource under a prefix the slice never
sees and cannot escape. First-party surfaces and add-ons use the same contract. The
difference is the prefix: a first-party slice owns a bare namespace such as
`association:`; anything registered through the public section hook is placed under
`addon:{key}:`.

On the client, a component binds to a slice through a scoped composable, and from then
on autosave, restore, dirty state, conflict detection, 422 error mapping, and the atomic
commit apply to the slice's fields exactly as they apply to the resource's own columns.
A first-party card and an add-on's slot component take the same path.

### Key scheme

Every slice field key is `{namespace}:{field}`:

- `{namespace}` is the slice's key, `[a-z0-9_-]+`, unique per model. First-party slices
  registered by the panel's own sections use a bare namespace (`attribute`, `channel`,
  `association`, `url`, `price`, `media`). Slices registered through the public
  `Section::formExtensions()` hook are namespaced `addon:{key}`, so their full keys are
  `addon:{key}:{field}`. The `addon:` prefix is reserved and cannot be claimed as a bare
  namespace.
- `{field}` is the slice's own field name, free-form. A slice with one value per row
  (an availability channel, a price tuple, a URL language) uses the row identity as the
  field name, which is how the existing `channel:{id}` keys already work; a slice that
  nests another surface (the sole variant's `attribute:{handle}` values) carries that
  surface's keys as its field names.
- The slice handles only `{field}`. Prefixing and unprefixing happen in the composer, so
  a slice has no API through which it could name a key outside its namespace. The
  isolation guarantee is structural, not a runtime check.

There is no technical way to stop an add-on's service provider from registering a bare
namespace, since it runs with the same privileges as the panel's own. The public hook
simply does not offer it. That is the same trust model the panel already relies on for
container bindings and page overrides.

### Server: the `FormSlice` and `DraftSlice` contracts

New `Lunar\Panel\Contracts\FormSlice`, with an abstract `Lunar\Panel\Forms\FormSlice`
supplying `normalize()` and `labels()` no-op defaults as `Drafts\DraftableResource` does.
`Lunar\Panel\Contracts\DraftSlice extends FormSlice` adds the one draft-specific hook,
`discard()`, with an abstract `Lunar\Panel\Drafts\DraftSlice` defaulting it to a no-op.
A plain `FormSlice` composes into a draft unchanged; the split exists so the same slice
class serves a drafted edit page and a plain form without a second contract:

- `model(): class-string<Model>` — the draftable model this contributes to.
- `key(): string` — the namespace.
- `fields(Model $record): array<int, string>` — the unprefixed field names for this
  record. Takes the record because row-shaped slices derive their field set from data
  (which channels exist, which currencies are enabled, which collections the model's
  media definition declares).
- `currentValues(Model $record): array<string, mixed>` — the current, normalised stored
  value per field, keyed by unprefixed name.
- `normalize(array $data): array` — same contract as the resource's, over the slice's
  own values.
- `rules(Model $record): array<string, mixed>` — rules keyed by unprefixed field. The
  composer prefixes the keys. Rule parameters pass through verbatim, so a rule may
  reference a resource field by its real key (`required_if:brand_id,...`), which is
  read-only by construction. Referencing one of the slice's own fields in a parameter
  needs the full key; `FormSlice::field(string $name): string` returns it.
- `commit(Model $record, array $values): void` — receives the slice's own values,
  unprefixed, every field present (current values overlaid with the draft), after the
  resource's own commit, inside the same transaction. Persists through core actions.
- `labels(): array<string, string>` — unprefixed field to lang key for the conflict
  dialog and validation messages.
- `discard(Model $record, EditDraft $draft): void` — on `DraftSlice` only, default
  no-op, called when a draft holding this slice's keys is discarded or pruned. Exists for
  slices that hold state outside the JSON columns (staged media uploads in
  [[0087-product-editing-through-the-draft]]).

### Server: registration and composition

- First-party slices register from the panel's own sections through
  `Section::formSlices(): array<int, class-string<FormSlice>>`. Add-ons register
  through `Section::formExtensions()` with the same return type; `PanelManager` places
  those under `addon:{key}`. Both hooks follow the optional-hook pattern of
  `draftables()`.
- `PanelManager::formSlice(string $class, bool $addon)` resolves the class from the
  container and indexes it by `model()` then namespace. A duplicate namespace on the
  same model throws at boot, naming both classes.
- Slices are stored separately from draftables so registration order between the
  panel's own sections and add-on sections does not matter. `draftableFor()` composes
  lazily: a model with no slices returns its `DraftableResource` unchanged; otherwise a
  `Drafts\ComposedDraftResource` wrapping the resource and its slices.
- `ComposedDraftResource` implements `DraftableResource` and is the only place prefixing
  lives:
  - `fields()` — the resource's fields plus every slice's, prefixed.
  - `currentValues()` / `rules()` / `labels()` — the resource's plus each slice's with
    keys prefixed.
  - `normalize()` — routes each namespace's keys to its slice, everything else to the
    resource.
  - `commit()` — the resource's `commit()` with its own keys, then each slice's
    `commit()` with its unprefixed values, in registration order. `DraftManager::commit()`
    already wraps the resource commit in a transaction, so the composed commit is atomic
    with no change to the manager.
- Discard fans out through the `EditDraft` model's `deleting` event, wired in the panel's
  service provider, so every path that removes a draft (the discard endpoint, pruning, the
  record-deleted cleanup) reaches the slices whose keys the draft held. A committed draft
  is consumed rather than discarded and deletes quietly. Pruning therefore moves
  `EditDraft` from `MassPrunable` to `Prunable`, and the record-deleted cleanup deletes
  drafts one model at a time, so each is an instance the hook can receive.
- `EditDraftController`, the routes, and the 200/409/422 payloads do not change. The
  manager only ever sees a `DraftableResource`.

### Server: stale slice keys

A stored draft can carry keys whose namespace is no longer registered (an add-on was
removed, a channel was deleted, a slice dropped a field). Today such a key surfaces at
commit as a spurious conflict or an unknown field, leaving every record with such a draft
unsavable. `DraftManager::commit()` drops stored keys the resource no longer declares
before overlaying the request's diff, so the draft's remaining fields commit normally.
Incoming data is still checked against the declared field set as it is now; that path
guards against a bad client, not a removed package or a deleted row. Autosave already
self-heals, since the client only restores and resends keys the page seeded.

### Server: seeding the page

`useEditDraft` restores a draft only into keys already present in `initial`, and diffs
only those keys, so the page must know the slice fields and their current values up
front. Rather than touch every edit controller, `HandlePanelInertiaRequests` shares a
lazy `formSliceValues` prop: the prefixed current values of every slice on the current
record, or an empty object when the page has no record or no slices apply. The record
is the deepest route-bound model, matching `EditDraftController::draftable()`. The
middleware's existing `currentRecord()` returns the first bound model, which is the
parent on nested routes such as a product's variant, so this prop must not reuse it.

Edit controllers that hand-build slice values today (`attributeValues`,
`availabilityValues`, `variantValues` on the product page) stop doing so once those
surfaces become slices; the shared prop replaces them.

### Client: `useEditDraft` changes

- Merges `formSliceValues` into `initial` before building `pristine` and `values`, so
  slice keys autosave, restore, diff, and guard like any other. A `slices` option
  (default `true`) opts a form out, for a page that drafts a record other than the
  route's deepest binding.
- Provides itself under the `sliceFormKey` injection key when created inside a
  component, as a `SliceForm` (`values`, `errors`, `dirtyKeys`, `saving`,
  `committing`), so components further down the tree can find the page's form. A
  plain page form can provide the same shape ([[0088-panel-form-slices]]).

### Client: `useFormSlice(namespace)`

New `resources/js/composables/useFormSlice.ts`, exported on `ui.ts` and the mirrored
`@lunarphp/panel` index. First-party cards call it with a bare namespace
(`useFormSlice('association')`); an add-on's slot component calls it with its key and
the composable applies the `addon:` prefix (`useFormSlice('example-addon')` resolves
to `addon:example-addon:`). Callable from any component inside the page's tree, which
every `PageZone` on an edit page is:

- Injects the page's draft form; throws a descriptive error when the page has none.
- Returns `{ values, errors, field, isDirty, saving, committing }`:
  - `values` — a typed reactive proxy scoped to the namespace, so
    `v-model="slice.values.tier"` reads and writes `addon:example-addon:tier` on the
    page's form. Enumeration lists only the namespace's fields.
  - `errors` — the form's 422 errors filtered to the namespace and unprefixed.
  - `field(name)` — a `WritableComputedRef` for one field.
  - `isDirty` — whether any of the namespace's fields differ from pristine.
  - `saving` / `committing` — the form's refs, passed through.
- The composable never exposes the underlying form, so a component has no path to
  another namespace's keys or to resource keys. The server enforces the same boundary
  independently.

### Conflicts and validation

A conflicting slice field appears in the existing `DraftConflictDialog` with the slice's
translated label; a 422 maps to `errors[field]` in the bound component. Structured
values (a media list, a price tuple) need a readable presentation in the dialog, which
[[0087-product-editing-through-the-draft]] specifies alongside the slices that need it.

### Example add-on

`packages/panel-addon-example` gains a `LoyaltyTierSlice` on `Customer` storing a
loyalty tier under the customer's `meta` column (via `UpdatesCustomer`, so the example
stays schema-free), and a `LoyaltyCard.vue` slot component in the
`customers.edit:main:after` zone that binds to it with `useFormSlice('example-addon')`.
The README's extension guide gains a section walking through both, and
`tests/panel/Feature/ExampleAddonTest.php` exercises the whole path against the real
customer routes: autosave stores the prefixed key, a concurrent change to the tier
surfaces as a conflict, and a clean commit persists it in the same transaction as the
first-party fields.

## Alternatives considered

- **A field-level hook into first-party forms** (hide, disable, relax required, replace;
  the Filament `extendForm` model and the ask in lunar#2736) — rejected. Mutation of a
  structure the add-on does not own is exactly the operation that cannot compose across
  add-ons. Hiding a first-party field is a per-store decision that belongs to the host
  and is out of scope here (see References).
- **Lifecycle hooks for slot components** (before-save, after-save, discard events; the
  slot persists to its own endpoint) — rejected. Hooks leave ordering and atomicity to
  each add-on, give the add-on's data none of the draft layer's guarantees, and need a
  separate dirty-state registration. Riding the draft gets all of it, and the slice's
  `commit()` is the hook. For "react after a save" a server-side commit event is the
  right tool and can be added independently.
- **An add-on-only contract, leaving first-party slices hand-rolled** — rejected. The
  product resource already contains three slices written by hand, the product page needs
  four more, and the brand, collection, and product-type pages share two of them. One
  contract used by both sides is smaller, and first-party use is the proof that the
  add-on surface is complete.
- **Slices declare prefixed keys themselves** — rejected. It turns a structural
  guarantee into a runtime check and makes every slice method deal in prefixes.
- **Per-page explicit seeding** (each edit controller merges slice values into its own
  props) — rejected. Every controller would need the same lines, and the middleware
  already resolves the current record. One shared prop keeps slice support automatic
  for any draft-backed page, first-party or add-on.
- **Slices on create pages** — deferred. Create pages post a plain form and redirect;
  there is no draft and no record until the store succeeds.
- **Do nothing** — rejected. Add-ons fork the page by registering a component under the
  first-party page name, which breaks on every panel upgrade, and first-party
  sub-surfaces keep saving outside the draft.

## Migration impact

- **Database**: none. Slice values live in the existing `edit_drafts` JSON columns under
  prefixed keys, and a slice persists committed values wherever it chooses.
- **Breaking changes**: none to the public surface. `ProductDraftResource` shrinks as its
  hand-rolled slices migrate to the contract, but it is internal. `EditDraft` moves from
  `MassPrunable` to `Prunable`. The product edit page's `attributeValues`,
  `availabilityValues` and `variantValues` props go away, replaced by the shared
  `formSliceValues` prop.
- **Upgrade path**: none required.
- **Translations**: no new panel copy. A slice's labels come from its own lang group
  (an add-on's via `Section::langNamespaces()`). The example add-on's `en` and `fr`
  groups gain the loyalty-tier label.
- **Filament / admin impact**: none.
- **Public contract surface** (treated as contract from first release): the
  `FormSlice` and `DraftSlice` contracts and abstracts, `Section::formSlices()` and
  `Section::formExtensions()`, the `{namespace}:{field}` and `addon:{key}:{field}` key
  schemes, the `formSliceValues` shared prop, `useFormSlice` and its return shape, the
  `SliceForm` shape and `sliceFormKey`, and the `slices` option on `useEditDraft`.
  `ComposedDraftResource` is internal.

## Open questions

- **Permission on a slice.** The page route's `can:` middleware already gates every
  draft endpoint, and a slot carries its own `permission`. Is a per-slice permission
  worth adding so a staff member who cannot see the component also cannot commit its
  keys? Owner: Glenn. Lean: route gate is enough; add later if an add-on needs it.
- **Cross-field rules inside a slice.** Prefixed parameters via `FormSlice::field()`
  are workable but easy to forget. Should the composer rewrite bare parameters that
  match a slice field name? Owner: Glenn. Lean: no; the helper is explicit.
- **Example add-on storage.** Writing the loyalty tier into `Customer::$meta` keeps the
  example schema-free but demonstrates a pattern a real add-on should avoid. Confirm
  this is acceptable for a reference implementation, or give the example its own table.
  Owner: Glenn.

## References

- [lunar#2736](https://github.com/lunarphp/lunar/discussions/2736) — the request that
  prompted this spec, and the reply setting out the "add-ons add, the host subtracts"
  rule.
- [[0087-product-editing-through-the-draft]] — the first-party consumer: associations,
  slugs, prices, and media as slices, and the operations that stay immediate.
- [[0088-panel-form-slices]] — the same contract on the panel's plain forms: create
  pages and settings forms.
- [[0051-panel-edit-drafts]] — the draft layer this composes into.
- [[0049-inertia-panel]] — the additive extension surface, `Section` hooks, slots and
  zones, `ui.ts` exports, and the rejection of a tabs extension point on the same
  grounds this spec applies to forms.
- [[0057-panel-products-section]] — `ProductDraftResource`, whose attribute,
  availability, and variant prefixes are the precedent for namespaced slices.
- Host-level hiding of first-party fields is the other half of the discussion reply. It
  is a separate, config-driven change and gets its own spec if it goes ahead.

## Implementation plan

- [x] Slice 1 — Server composition: `FormSlice` and `DraftSlice` contracts and abstracts,
  `ComposedDraftResource` (prefixing, routing, stale-key pruning, ordered commit,
  discard fan-out), `Section::formSlices()` / `formExtensions()` and
  `PanelManager::formSlice()` with lazy composition in `draftableFor()`, namespace and
  field validation at registration, `EditDraft` to `Prunable`; unit tests covering prefix
  round-trips, duplicate-namespace rejection, reserved `addon:` namespace, stale-key
  pruning, rule-key prefixing, commit ordering inside one transaction, and discard
  fan-out on discard and prune.
- [x] Slice 2 — Page seeding: `formSliceValues` shared prop resolved from the deepest
  route-bound model; feature test that a product and a variant edit page each seed the
  right record's slice values.
- [x] Slice 3 — Client: `useEditDraft` merges the shared prop (with the `slices`
  opt-out) and provides itself; `useFormSlice` with the scoped proxy, errors, `field()`,
  and dirty/saving passthroughs; `ui.ts` and `@lunarphp/panel` exports; vitest coverage
  for scoping, the `addon:` resolution, restore, and error mapping.
- [x] Slice 4 — Migrate the product resource's hand-rolled surfaces (attributes,
  availability, sole variant) onto `FormSlice` classes registered via
  `formSlices()`, and drop the per-page value props they replace. No behaviour change;
  existing draft tests must pass unchanged.
- [x] Slice 5 — Example add-on and guide: `LoyaltyTierSlice`, `LoyaltyCard.vue`,
  README section, `en`/`fr` label, and the end-to-end path in `ExampleAddonTest`
  (autosave, conflict, commit).
