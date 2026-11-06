# 0087 — Product editing through the draft

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-16
- TODO item: Product editing through the draft — associations, slugs, prices, and media join the product draft as slices; the few operations that stay immediate say so (spec 0087)

## Problem

The product edit page has two save models and does not say which one a given control
uses. Name, status, type, brand, descriptions, tags, collections, attributes,
availability, and the simple-shape variant fields go into the draft and land with the
save cluster. Associations, URL slugs, prices, and media persist the moment they are
touched, with no toast, no entry in the dirty state, and no place in the restore banner
or the conflict dialog. They sit on the same form as the drafted fields and look
identical.

Staff reasonably expect that nothing on an edit page takes effect until they save. Today
that is true for some cards and false for others, and the false ones include the
irreversible action of deleting an image.

The products spec chose immediate persistence for these surfaces on the grounds that
rows without an identity until saved merge badly. That holds for the design it had in
hand. It does not hold once each surface has a stable key: an association type is a
sorted id list exactly like `collection_ids`; a URL row is identified by its language;
a price row by its currency, customer group, and minimum quantity; a media item by its
id, with uploads staged before they have one. With [[0086-panel-draft-slices]]
providing the slice contract, each of these is a small class rather than a special case.

The brand, collection, and product-type edit pages share the media and slug components,
so they have the same split and get the same fix.

## Proposal

One rule, stated on the page: **an edit is part of the draft and lands when you save; an
operation applies immediately and says so.** Every control on the product and variant
edit pages is one or the other.

Edits: everything that changes the record's own state, including its associations,
slugs, prices, and media. These become `DraftSlice` classes composed into the product
and variant resources (and, for media and slugs, the brand, collection, and product-type
resources).

Operations: the things that change the world beyond the record's fields. Recording a
stock movement, generating or regenerating variants, collapsing to the simple shape,
bulk variant actions, duplicating, and deleting. These keep their immediate endpoints and
gain a consistent affordance so nobody mistakes them for drafted edits.

### Slices

Each slice follows the [[0086-panel-draft-slices]] contract. Field names are the
row identity; the value is the row's editable state; a null value marks a row for
removal at commit. Commit diffs the drafted set against the current rows and applies
creates, updates, and deletes through the existing core actions.

**`association`** — on `Product`. One field per association type (`alternate`,
`cross-sell`, `up-sell`), value an ordered list of product ids. Normalisation drops
duplicates and the product's own id. Commit syncs the relation per type and writes the
order as position. Conflicts are per type. Commits through the existing association
actions.

**`url`** — on every `HasUrls` draftable (product, variant, brand, collection,
product type). One field per language handle, value an ordered list of
`{id: int|null, slug: string, default: bool}`. New rows have a null id and are created at
commit; rows absent from the list are deleted. Rules enforce one default per language,
slug format, and uniqueness against other elements' URLs for that language at commit
time, so the conflict is reported as a validation error rather than a database
exception. Commits through `CreatesUrl`, `UpdatesUrl`, `DeletesUrl`.

**`price`** — on `ProductVariant`, riding the product draft under the `variant:` slice
on simple-shape products the way the variant's other fields do. One field per
`{currency_code}:{customer_group_id|0}:{min_quantity}` tuple, value
`{price: int, list_price: int|null}` in minor units. Changing a tier's quantity or group
is a removal plus an addition, which is what it is from the other editor's point of view
too. Rules require a base row (group 0, quantity 1) per enabled currency. The composer
guarantees the tuple is unique by construction, so the duplicate-tier case the products
spec worried about cannot arise. Commits through the price actions.

**`media`** — on every `HasMedia` draftable. One field per media collection the model's
media definition declares, value an ordered list of
`{id: int, primary: bool, properties: object}` where `properties` carries alt text and
any custom properties `UpdatesMedia` manages. Removal from the list deletes the media at
commit; this is where the irreversible step moves to.

Uploads are staged. The upload endpoint attaches the file to the staff member's draft
row for the record (creating the draft if none exists) rather than to the record, and
returns the staged item's id and preview URL; the client appends it to the collection's
list. `EditDraft` implements `HasMedia` for this. Nothing outside the draft can see a
staged file: it is not in the record's collections, so storefront and search are
unaffected. On commit the slice moves each staged item onto the record with the media
library's move operation, then applies order, primary, and properties through
`ReordersMedia` and `UpdatesMedia`. On discard or prune, the slice's `discard()` hook
deletes the draft's staged media. Conversions run at upload as they do now, so
thumbnails work while staged.

**`variant_media`** — on `ProductVariant`. A single ordered list of media ids from the
product's pool with a primary flag, replacing the immediate sync endpoint. Rules require
every id to belong to the parent product.

### Components

The affected components keep their markup and interaction design and swap their
persistence: instead of posting, each binds to its slice through `useDraftSlice()` and
mutates the values object. Reordering associations, editing a slug, typing a price, or
dragging an image now dirties the form, shows in the save cluster, survives navigation
through the restore banner, and lands atomically with the rest of the record.

- `UrlSlugs`, `PricingEditor`, `MediaGroups`, and the associations card on the product
  page drop their `router.*` calls and debounce timers.
- `MediaGroups` keeps its upload control, which posts to the staging endpoint and
  appends the result to the slice; everything after upload is a draft edit.
- The variant page's media picker binds to `variant_media`.
- Optimistic reorder handling, which existed to stop rows snapping back mid-request,
  goes away because there is no request.

### Conflict dialog for structured values

`DraftConflictDialog` today renders a field's mine/base/theirs values as text and offers
a manual merge input. That is right for scalars and translated text and wrong for a list
of media items or a price tuple. The dialog gains a per-namespace summary presentation:
a short human description of each side (a slug, an amount with currency, a count of
items with thumbnails for media, product names for associations) and keep-mine /
take-theirs only, with no manual merge input. First-party slices ship their summary
renderer; a slice without one falls back to the JSON-ish text rendering, which is what
add-ons get until they register one. Registering a renderer for an add-on slice is out
of scope here and can be added additively.

### Operations that stay immediate

| Operation | Where | Why it is an operation |
|---|---|---|
| Stock adjustment | Inventory card, both pages | Records a movement in the ledger |
| Generate, regenerate, collapse to simple | Options builder | Creates and destroys variant rows |
| Bulk enable, disable, delete, set price, adjust stock | Variants table | Acts on many records at once |
| Duplicate product | Page actions | Creates a new record |
| Delete product, delete variant | Page actions | Destroys the record |
| File upload | Media groups | The bytes land in storage; the link is drafted |

Every card or section containing one of these carries the same affordance: a small
"Applies immediately" marker next to the control (new `drafts.applies_immediately`
lang key, with a tooltip explaining that the action is not part of the draft),
destructive ones confirm before running as they do today, and every one flashes a
success message on completion. Bulk set-price is the one operation that looks like a
value edit; it keeps a confirmation step for that reason.

File upload is the exception to the marker: from the user's point of view it is part of
the draft, because nothing about the record changes until save. Only the file's arrival
in storage is immediate, and that is invisible.

### Routes

The nested `associations.*`, `urls.*`, `media.update|reorder|destroy`,
`variants.prices.*`, and `variants.media.sync` routes are removed. `media.store` becomes
the staging upload endpoint. The draft trio, `options.generate`, `variants.bulk`,
`variants.stock.adjust`, `duplicate`, and `destroy` are unchanged.

## Alternatives considered

- **Keep the split and label the immediate cards** — rejected. Labelling would make the
  current behaviour honest but not sensible: deleting an image would still be
  irreversible on click, and two staff editing prices would still overwrite each other
  with no conflict detection.
- **Draft everything, including stock and generation** — rejected. A stock adjustment
  is a movement with its own audit trail, not a field, and drafting it would mean
  drafting a ledger entry. Generation restructures rows other staff may be editing and
  cannot merge field-wise; the products spec's reasoning stands.
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

- **Database**: none. Staged media use the media table's existing polymorphic owner
  columns. Drafts stay in `edit_drafts`.
- **Breaking changes**: the removed nested product routes were listed as public surface
  in the brands and products specs. v2 is unreleased, so this is a pre-release contract
  change rather than a break for consumers; add-ons that called those endpoints directly
  (none known) move to the draft. `EditDraft` gains `HasMedia` and becomes `Prunable`
  (from [[0086-panel-draft-slices]]).
- **Upgrade path**: none required.
- **Translations**: new `drafts.applies_immediately` and its tooltip, plus the conflict
  summary phrases for the four first-party slices, across all 16 panel locales. The
  removed endpoints' flash keys (`pricing.flash_*`, `products.flash_associations_added`,
  `media.flash_updated|deleted|reordered`) are deleted; `media.flash_uploaded` is kept
  for the staging response.
- **Filament / admin impact**: none. The Filament admin's product resource is untouched.
  A Filament user editing prices while a panel user drafts them is exactly the
  concurrent case the commit check now catches for prices too.
- **Public contract surface**: the slice namespaces `association`, `url`, `price`,
  `media`, and `variant_media` and their value shapes; the staging upload endpoint and
  its response shape; the `drafts.applies_immediately` affordance as the convention for
  any future immediate operation on an edit page.

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

- [[0086-panel-draft-slices]] — the `DraftSlice` contract, composer, seeding,
  `useDraftSlice`, and the discard hook this spec relies on.
- [[0051-panel-edit-drafts]] — the draft layer, key scheme, conflict and rebase protocol.
- [[0057-panel-products-section]] — the products page and the original reasoning for
  immediate sub-resources, revisited here.
- [[0052-panel-brands-section]] — the shared media and slug surfaces and their actions.
- [[0060-panel-media-groups]] — media collections on catalog edit screens.
- [lunar#2736](https://github.com/lunarphp/lunar/discussions/2736) — the discussion
  that surfaced the split save model.

## Implementation plan

- [ ] Slice 1 — Affordance and rule: the `drafts.applies_immediately` marker with
  tooltip on every immediate operation on the product and variant pages, confirmation
  on bulk set-price, flash on every operation; 16-locale copy; page tests asserting each
  operation carries the marker. Lands first so the page is honest before any surface
  moves.
- [ ] Slice 2 — `association` slice, the associations card on `useDraftSlice`, removal
  of the association routes and controller; draft feature tests (autosave, per-type
  conflict, commit ordering) and a vitest pass over the card.
- [ ] Slice 3 — `url` slice on every `HasUrls` draftable, `UrlSlugs` on `useDraftSlice`,
  removal of the URL routes; uniqueness and one-default rules; tests across products,
  variants, brands, collections, product types.
- [ ] Slice 4 — `price` slice on the variant resource and under `variant:` on the
  product resource, `PricingEditor` on `useDraftSlice`, removal of the price routes;
  tuple-key round-trip, base-row rules, minor-unit normalisation, conflict per tuple.
- [ ] Slice 5 — Media staging: `EditDraft` as `HasMedia`, the staging upload endpoint,
  the `media` slice on every `HasMedia` draftable with move-on-commit and
  delete-on-discard, `variant_media` slice, `MediaGroups` and the variant picker on
  `useDraftSlice`, removal of the media update/reorder/destroy and variant sync routes;
  tests for staging visibility, commit move, discard and prune cleanup, conflict per
  collection.
- [ ] Slice 6 — Conflict dialog summary presentation for the four first-party slices;
  vitest coverage and a page test that a structured conflict renders the summary and
  offers no manual merge.
