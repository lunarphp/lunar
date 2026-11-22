# 0087 — Product editing through the draft

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-16
- TODO item: Product editing through the draft — associations, slugs, prices, and media join the product draft as slices; the few operations that stay immediate say so (spec 0087)

## Problem

The catalog and customer edit pages have more than one save model and do not say which
one a given control uses. On the product page, name, status, type, brand, descriptions,
tags, collections, attributes, availability, and the simple-shape variant fields go into
the draft and land with the save cluster. Associations, URL slugs, prices, images, and
files persist the moment they are touched, with no entry in the dirty state and no place
in the conflict dialog. The variant page's image picker has a third model: its own
local Save button, outside both the draft and the leave guard. They all sit on the same
form and look identical.

Staff reasonably expect that nothing on an edit page takes effect until they save. Today
that is true for some cards and false for others. Drafting a name change, changing a
price, and clicking Discard reverts the name and keeps the price. Deleting an image is
irreversible on click.

The products spec chose immediate persistence for these surfaces on the grounds that
rows without an identity until saved merge badly. That holds for the design it had in
hand. It does not hold once each surface has a stable key: an association type is a
sorted id list exactly like `collection_ids`; a URL row is identified by its language;
a price row by its currency, customer group, and minimum quantity; a media item by its
id, with uploads staged before they have one. With [[0086-panel-draft-slices]]
providing the slice contract, each of these is a small class rather than a special case.

Moving these surfaces into the draft also moves them into the draft layer's conflict
handling, which today loses updates in several paths (see "Draft layer correctness"
below). Associations and prices are last-write-wins endpoints now; putting them behind a
conflict check that can silently commit the wrong side is not an improvement, so the
draft layer is fixed first.

The brand and collection edit pages share the media and slug components, and the
product-type page shares the media components, so they have the same split and get the
same fix. The collection and customer pages also carry immediate controls of their own,
covered under "Operations that stay immediate".

## Proposal

One rule, stated on the page: **an edit is part of the draft and lands when you save; an
operation applies immediately and says so.** Every control on the product, variant,
brand, collection, product-type, and customer edit pages is one or the other.

Edits: everything that changes the record's own state, including its associations,
slugs, prices, media, and a customer's admin notes. These become `DraftSlice` classes
(or, for admin notes, a resource field) composed into the draft resources.

Operations: the things that change the world beyond the record's fields. Recording a
stock movement, generating or regenerating variants, collapsing to the simple shape,
bulk variant actions, defining a bundle, moving a collection in its tree, changing a
collection's product membership, managing a customer's addresses and linked users, and
deleting. These keep their immediate endpoints and gain a consistent affordance so
nobody mistakes them for drafted edits.

### Draft layer correctness

The draft layer from [[0051-panel-edit-drafts]] gets these changes before any surface
moves. Each closes a path where a commit writes a value the staff member did not choose
or overwrites a colleague without a conflict.

- **Base from the client.** `DraftManager::snapshotFor()` takes the base of a newly
  drafted key from the record at the moment of the PATCH or commit. When a colleague
  commits between page load and the first autosave, the colleague's value becomes the
  base, and the commit overwrites it with no conflict; a Save before any autosave can
  never conflict. The client instead sends `base: {key: value}` with each PATCH and
  commit for the keys it drafts — its pristine value, which is what the staff member was
  shown — and the server stores that as the key's snapshot when the key is first
  drafted. Existing snapshot entries stay fixed for the life of the draft, as now.
- **The client's diff is authoritative at commit.** Commit overlays the request's diff
  on the stored draft (`[...$draft->data, ...$data]`). A key the client now considers
  clean is absent from its diff, so the stored value is committed anyway: a field
  reverted just before Save commits the reverted-away value, and choosing "take theirs"
  in the conflict dialog commits "mine", because the resolved value equals pristine and
  drops out of the diff. Commit replaces the stored draft with the request's diff, the
  same way autosave does.
- **Draft version precondition.** The overlay exists so a commit does not drop fields
  drafted in another tab by the same staff member. Autosave's wholesale replace already
  drops them, and resets their snapshots, so the overlay protects only half the path.
  `EditDraft` gains a `version` counter (baseline migration while v2 is in alpha). PATCH
  and commit send the version the client last saw; a mismatch returns 409 with
  `{stale_draft: true}`, and the page tells the staff member the draft changed in
  another tab and offers to reload it. Reloading replaces this tab's unsent edits with
  the stored draft; there is no per-field merge between tabs. Same-tab requests are
  already serialised.
- **No conflict when both sides agree.** `detectConflicts()` reports a key whenever
  base and theirs differ. A key where mine equals theirs is not a conflict; its snapshot
  is rebased silently.
- **Conflict check under lock.** Conflict detection runs outside the commit
  transaction. Commit re-reads `currentValues()` inside the transaction after locking
  the record row (`lockForUpdate`) and detects conflicts there, so a commit landing in
  between cannot be overwritten.
- **Pristine tracks what was sent.** After a successful commit, `useEditDraft` copies
  `values` into pristine, which marks edits typed while the request was in flight as
  clean although they were never sent. Pristine takes the values from the diff that was
  sent, and keys edited since then stay dirty and autosave.
- **Failed commits keep the client in step.** Commit persists the draft before
  validating, so a 422 or 409 leaves a server draft the client does not know about
  (`hasDraft` stays false), and a later manual revert never sends the DELETE. Every
  commit response that is not a success sets `hasDraft`. Transport errors (419, 500)
  show an error toast instead of escaping as an unhandled rejection; autosave failures
  show the same toast and keep the form dirty.
- **The key set follows the page.** `useEditDraft` reads its initial values and
  `formSliceValues` once. An operation that changes which keys the page has — collapsing
  to the simple shape adds `variant:*`, generating variants removes them — reloads props
  with `preserveState`, so new keys never enter `values` or pristine (edits to them are
  never dirty, autosaved, or committed) and removed keys stay drafted (every commit then
  fails `prohibited` validation on a card that is no longer rendered). When the server's
  key set changes, `useEditDraft` adds new keys with their server values as pristine and
  drops removed keys from `values`, pristine, and the next autosave. A structural
  operation that removes drafted keys warns that those edits are dropped before it runs.
- **Row keys and removal.** Pristine and the diff only see keys present at load, and
  `assertKnownFields` rejects keys `fields($record)` does not list. The slices below add
  keys at runtime (a new price tuple). `DraftableResource` gains
  `acceptsField(Model $record, string $key): bool`, defaulting to membership of
  `fields()`, which a slice overrides with a key pattern; the client accepts a new key
  whose pristine value is `null` and drafts it like any other. A `null` value marks a
  row for removal, and normalisation on both sides treats a removed row and an absent row
  as equal.
- **Restore banner.** `restoredFrom` is computed but rendered nowhere. Every drafted
  edit page renders the restore banner that [[0051-panel-edit-drafts]] specifies.
- **Manual merge keeps types.** The conflict dialog's manual merge input coerces the
  typed text back to the type of the field's base value, so a boolean or number is not
  committed as a string.

### Slices

Each slice follows the [[0086-panel-draft-slices]] contract. Field names are the
row identity; the value is the row's editable state; a null value marks a row for
removal at commit. Commit diffs the drafted set against the current rows and applies
creates, updates, and deletes through core actions.

**`association`** — on `Product`. One field per association type (`alternate`,
`cross-sell`, `up-sell`), value an ordered list of product ids. Normalisation drops
duplicates and the product's own id. Commit syncs the relation per type and writes the
order as sort. Conflicts are per type. Core has no synchronous association action:
`Product::associate()` and `dissociate()` dispatch the queued `Associate` and
`Dissociate` jobs, so the panel reports "Products linked." before anything is saved,
and two picks before a worker runs create duplicate rows. This slice adds a
`SyncsProductAssociations` action contract (`execute(Product $product, string $type,
array $targetIds)`) with a `syncAssociations()` verb on `Product`, and the jobs delegate
to it. The slice commits through it inside the draft transaction.

**`url`** — on every `HasUrls` draftable: product, brand, and collection. (Product
variants and product types do not use `HasUrls`.) One field per language handle, value
an ordered list of `{id: int|null, slug: string, default: bool}`. New rows have a null
id and are created at commit; rows absent from the list are deleted. Rules enforce one
default per language, slug format, and uniqueness against other elements' URLs for that
language at commit time, so the conflict is reported as a validation error rather than a
database exception. Commits through `CreatesUrl`, `UpdatesUrl`, `DeletesUrl`.

**`price`** — on `ProductVariant`, riding the product draft under the `variant:` slice
on simple-shape products the way the variant's other fields do. One field per
`{currency_code}:{customer_group_id|0}:{min_quantity}` tuple, value
`{price: int, list_price: int|null}` in minor units, or null to remove the tier.
Changing a tier's quantity or group is a removal plus an addition, which is what it is
from the other editor's point of view too. Rules require a base row (group 0, quantity
1) per enabled currency. The composer guarantees the tuple is unique by construction, so
the duplicate-tier case cannot arise. Core has no price write action today
(`ProductPriceController` calls `prices()->create()` and `update()` directly); this
slice adds a `SyncsPrices` action contract with a `syncPrices()` verb on `HasPrices`
models and commits through it. Validation errors render on the row that caused them.

Bundle repricing (`RepriceBundle`) rewrites a bundle variant's price rows outside the
draft. After it runs, the bundles card reloads the page props, and the `price` keys pick
up the new server values through the key-set refresh above; a drafted price for a tuple
the reprice changed conflicts on commit as any concurrent write does.

**`media`** — on every `HasMedia` draftable: product, brand, collection, and product
type. One field per media collection the model's media definition declares, covering
both image groups (`MediaManager`, `MediaEditDialog`) and file groups (`FileManager`,
`FileEditDialog`). The value is an ordered list of `{id: int, properties: object}`,
where `properties` carries the name, alt text, caption, focal point, and any custom
properties `UpdatesMedia` manages. Order is meaningful: the first image is the hero, as
the page shows today. Removal from the list deletes the media at commit; this is where
the irreversible step moves to.

Uploads are staged. The upload endpoint attaches the file to the staff member's draft
row for the record (creating the draft if none exists) rather than to the record, and
returns the staged item's id and preview URL; the client appends it to the collection's
list. `EditDraft` implements `HasMedia` for this. Nothing outside the draft can see a
staged file: it is not in the record's collections, so storefront and search are
unaffected. On commit the slice moves each staged item onto the record with the media
library's move operation, then applies order and properties through `ReordersMedia` and
`UpdatesMedia`. Staged items absent from the committed list are deleted.

Media file moves are not covered by the database transaction. The slice moves files
after the transaction commits (an `afterCommit` callback), so a failed commit leaves the
staged files on the draft, where a retry finds them.

Staged media is cleaned up on three paths: `discard()` when the draft row is deleted,
the commit hook above for items uploaded and then removed before Save, and a sweep
alongside draft pruning that deletes staged media whose draft no longer lists them
(covering a partial revert, where autosave rewrites the draft without the media keys and
neither other path reaches the slice). Committing a draft deletes it quietly, so the
commit path does not rely on the `deleting` event.

**`variant_media`** — on `ProductVariant`. A single ordered list of media ids from the
product's pool; the first is the variant's primary. It replaces the variant image
picker's local Save button and the immediate sync endpoint, so the picker dirties the
form and the leave guard covers it. Rules require every id to belong to the parent
product.

**`admin_notes`** — a field on the customer draft resource, not a slice: a nullable
string with the empty string normalised to null. The admin notes card loses its own
"Save changes" button and binds to the draft like the customer's other fields.

### Components

The affected components keep their markup and interaction design and swap their
persistence: instead of posting, each binds to its slice through `useFormSlice()` and
mutates the values object. Reordering associations, editing a slug, typing a price, or
dragging an image now dirties the form, shows in the save cluster, survives navigation
through the restore banner, and lands atomically with the rest of the record.

- `UrlSlugs`, `PricingEditor`, `MediaManager`, `MediaEditDialog`, `FileManager`,
  `FileEditDialog`, `VariantMediaPicker`, and the associations card on the product page
  drop their `router.*` calls, debounce timers, and local save buttons.
- `MediaGroups` keeps its upload controls, which post to the staging endpoint and
  append the result to the slice; everything after upload is a draft edit.
- Optimistic reorder handling, which existed to stop rows snapping back mid-request,
  goes away because there is no request.

### Conflict dialog for structured values

`DraftConflictDialog` today renders a field's mine/base/theirs values as text and offers
a manual merge input. That is right for scalars and translated text and wrong for a list
of media items or a price tuple, which render as `[object Object]`. The dialog gains a
per-namespace summary presentation: a short human description of each side (a slug, an
amount with currency, a count of items with thumbnails for media, product names for
associations) and keep-mine / take-theirs only, with no manual merge input. First-party
slices ship their summary renderer; a slice without one falls back to a JSON rendering,
which is what add-ons get until they register one. Registering a renderer for an add-on
slice is out of scope here and can be added additively.

### Operations that stay immediate

| Operation | Where | Why it is an operation |
|---|---|---|
| Stock adjustment | Inventory card, product and variant pages | Records a movement in the ledger |
| Generate, regenerate, collapse to simple | Options builder, product page | Creates and destroys variant rows |
| Bulk enable, disable, delete, set price, adjust stock | Variants table | Acts on many records at once |
| Define or remove a bundle, bundle pricing, components, groups | Bundles card, product and variant pages | Restructures the variant's pricing and stock from its components |
| Move in tree | Hierarchy card, collection page | Restructures a tree other staff may be editing |
| Add, remove, reorder products | Products card, collection page | Changes other records' membership; paginated and can run to hundreds |
| Add, edit, delete address; set default address | Addresses card, customer page | Each address is its own record used at checkout |
| Link, unlink user | Users card, customer page | Changes a storefront account's access |
| Delete | Page actions, every page | Destroys the record |
| File upload | Media groups | The bytes land in storage; the link is drafted |

Every card or section containing one of these carries the same affordance: a small
"Applies immediately" marker next to the control (new `drafts.applies_immediately`
lang key, with a tooltip explaining that the action is not part of the draft),
destructive ones confirm before running as they do today, and every one flashes a
success message on completion and renders its validation errors. Bulk set-price is the
one operation that looks like a value edit; it keeps a confirmation step for that
reason.

File upload is the exception to the marker: from the user's point of view it is part of
the draft, because nothing about the record changes until save. Only the file's arrival
in storage is immediate, and that is invisible.

Collection product reorder follows the drafted sort: when the drafted sort is not
"Custom", the drag handles are disabled with a hint, whatever the saved sort is.

### Routes

The nested `associations.*`, `urls.*`, `media.update|reorder|destroy`,
`variants.prices.*`, `variants.media.sync`, and `customers.notes.update` routes are
removed, along with the unused last-write-wins `products.update` and
`products.variants.update` routes, which bypass the draft and have no UI caller.
`media.store` becomes the staging upload endpoint. The draft trio, `options.generate`,
`variants.bulk`, `variants.stock.adjust`, `duplicate`, `destroy`, the bundles routes,
the collection `move` and `products.*` routes, and the customer `addresses.*` and
`users.*` routes are unchanged.

## Alternatives considered

- **Keep the split and label the immediate cards** — rejected. Labelling would make the
  current behaviour honest but not sensible: deleting an image would still be
  irreversible on click, and two staff editing prices would still overwrite each other
  with no conflict detection.
- **Draft everything, including stock and generation** — rejected. A stock adjustment
  is a movement with its own audit trail, not a field, and drafting it would mean
  drafting a ledger entry. Generation restructures rows other staff may be editing and
  cannot merge field-wise; the products spec's reasoning stands. Collection membership
  and customer addresses are rejected for the same reason: they change other records.
- **Keep the commit overlay and send a `clear: [keys]` list** — rejected. It fixes the
  reverted-field and take-theirs cases but leaves autosave and commit with different
  merge rules, and still loses another tab's work on the next autosave. One rule (the
  client's diff replaces the draft) plus a version check is simpler and covers both.
- **Snapshot the base at page load on the server** — rejected. The server would have to
  persist a snapshot for every page view, including views that never edit. The client
  already holds the value it showed.
- **Draft the media link but keep uploads on the record** — rejected. A file attached to
  the record on upload is visible to the storefront before the draft is saved, which is
  the very thing staff expect not to happen. Staging on the draft row costs one `HasMedia`
  implementation and a move at commit.
- **Stage uploads on the staff member rather than the draft** — rejected. The draft row
  is the natural owner: it already has the record and staff identity, its lifetime is
  the staging lifetime, and its prune and discard paths are where cleanup belongs.
- **Whole-table keys for prices** (`prices` as one list) — rejected. One key means any
  two staff touching pricing conflict on everything. Per-tuple keys conflict only on the
  row both touched, which is the draft layer's whole point.
- **Manual merge for structured conflicts** — rejected for now. A merge editor for a
  media list or a price row is a feature in itself; keep-mine / take-theirs covers the
  realistic case and matches how the products spec already treats availability rows.

## Migration impact

- **Database**: `edit_drafts` gains an unsigned integer `version`, folded into the
  baseline migration while v2 is in alpha. Staged media use the media table's existing
  polymorphic owner columns.
- **Breaking changes**: the removed nested routes were listed as public surface in the
  brands and products specs. v2 is unreleased, so this is a pre-release contract change
  rather than a break for consumers; add-ons that called those endpoints directly (none
  known) move to the draft. The draft PATCH and commit payloads gain `base` and
  `version`, and commit no longer overlays the stored draft; add-on slots bind through
  `useFormSlice()` and are unaffected. `EditDraft` gains `HasMedia` and `version`.
- **Core**: new `SyncsProductAssociations` and `SyncsPrices` action contracts, bound in
  `ActionServiceProvider`, with `Product::syncAssociations()` and `syncPrices()` verbs
  on `HasPrices` models and their contracts. The `Associate` and `Dissociate` jobs
  delegate to the association action.
- **Upgrade path**: none required.
- **Translations**: new `drafts.applies_immediately` and its tooltip, the stale-draft
  message, the dropped-keys warning before a structural operation, and the conflict summary phrases for the first-party
  slices, across all 16 panel locales. The removed endpoints' flash keys
  (`pricing.flash_*`, `products.flash_associations_added`,
  `products.flash_associations_reordered`, `products.flash_association_removed`,
  `products.flash_variant_media_updated`, `media.flash_updated|deleted`, and
  `customers.flash_notes_updated`) are deleted; `media.flash_uploaded` is kept for the staging
  response.
- **Filament / admin impact**: none. The Filament admin's product resource is untouched.
  A Filament user editing prices while a panel user drafts them is exactly the
  concurrent case the commit check now catches for prices too.
- **Public contract surface**: the slice namespaces `association`, `url`, `price`,
  `media`, and `variant_media` and their value shapes; the draft PATCH and commit
  payloads (`data`, `base`, `rebase`, `version`); `DraftableResource::acceptsField()`;
  the staging upload endpoint and its response shape; the two new action contracts and
  verbs; the `drafts.applies_immediately` affordance as the convention for any future
  immediate operation on an edit page.

## Open questions

- **Staged media and the media manager.** A draft's staged uploads appear only on the
  page where they were uploaded. Should the media manager, if it grows a library view,
  show staged items with a "pending" state, or hide them? Owner: Glenn. Lean: hide;
  they are not the record's media yet.
- **Bulk set-price as an operation.** It is the one immediate control that edits values.
  Keeping it immediate with confirmation is the proposal; revisit if staff find it
  surprising in use.
- **Slice migration order.** Media staging is the largest piece. Ship associations and
  slugs first to prove the pattern, then prices, then media? Or land all four together
  so the page has one save model from the first release that changes it? Owner: Glenn.
  Lean: ship in the plan's order below; each slice leaves the page consistent for the
  surfaces it covers, and the marker makes the remaining immediate ones explicit in the
  interim.

## References

- [[0086-panel-draft-slices]] — the `FormSlice` and `DraftSlice` contracts, composer,
  seeding, `useFormSlice`, and the discard hook this spec relies on.
- [[0051-panel-edit-drafts]] — the draft layer, key scheme, conflict and rebase protocol,
  and the restore banner.
- [[0057-panel-products-section]] — the products page and the original reasoning for
  immediate sub-resources, revisited here.
- [[0052-panel-brands-section]] — the shared media and slug surfaces and their actions.
- [[0060-panel-media-groups]] — media collections on catalog edit screens.
- [lunar#2736](https://github.com/lunarphp/lunar/discussions/2736) — the discussion
  that surfaced the split save model.

## Implementation plan

- [ ] Slice 0 — Draft layer correctness: client-supplied base, client diff authoritative
  at commit, draft `version` with the stale-draft response, no conflict when mine equals
  theirs, conflict check under lock, pristine from the sent diff, `hasDraft` and error
  toasts on failed commits and autosaves, key-set refresh after structural operations
  with the dropped-keys warning, `acceptsField()` and null-for-removal, the restore
  banner on every drafted page, typed manual merge; 16-locale copy. Pest regression
  tests on a generic draft resource for each lost-update path (take theirs, reverted
  field, colleague commit before first autosave, same-value conflict, failed commit,
  stale version) and vitest for `useEditDraft` (edits during an in-flight commit,
  key-set refresh). Lands first because every later slice relies on it.
- [ ] Slice 1 — Affordance and rule: the `drafts.applies_immediately` marker with
  tooltip on every immediate operation in the table above, confirmation on bulk
  set-price, flash and visible validation errors on every operation, collection reorder
  following the drafted sort; `admin_notes` as a customer draft field with the notes
  route removed; 16-locale copy; page tests asserting each operation carries the marker.
  Lands before any other surface moves so the pages are honest.
- [ ] Slice 2 — `SyncsProductAssociations` action and verb, the jobs delegating to it,
  the `association` slice, the associations card on `useFormSlice`, removal of the
  association routes and controller; draft feature tests (autosave, per-type conflict,
  commit ordering, no duplicate rows) and a vitest pass over the card.
- [ ] Slice 3 — `url` slice on product, brand, and collection, `UrlSlugs` on
  `useFormSlice`, removal of the URL routes; uniqueness and one-default rules; tests
  across the three resources.
- [ ] Slice 4 — `SyncsPrices` action and verb, the `price` slice on the variant
  resource and under `variant:` on the product resource, `PricingEditor` on
  `useFormSlice`, removal of the price routes and the unused `products.update` and
  `products.variants.update` routes, props reload after bundle repricing; tuple-key
  round-trip, adding and removing tiers, base-row rules, minor-unit normalisation,
  conflict per tuple, conflict after a reprice.
- [ ] Slice 5 — Media staging: `EditDraft` as `HasMedia`, the staging upload endpoint,
  the `media` slice on product, brand, collection, and product type covering image and
  file groups, move after commit, cleanup on discard, commit, and the prune sweep,
  `variant_media` slice, `MediaGroups` and the variant picker on `useFormSlice`, removal
  of the media update/reorder/destroy and variant sync routes; tests for staging
  visibility, commit move, each cleanup path, and conflict per collection.
- [ ] Slice 6 — Conflict dialog summary presentation for the first-party slices;
  vitest coverage and a page test that a structured conflict renders the summary and
  offers no manual merge.
