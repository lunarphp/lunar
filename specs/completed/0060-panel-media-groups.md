# 0060 — Panel media groups

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-22
- TODO item: Panel media groups — render every registered media collection on catalog edit screens, with a file uploader for non-image groups (spec 0060)

## Problem

Lunar lets a developer register additional media collections per model through a
`MediaDefinitions` class (`config('lunar.media.definitions')`), the seam v1 stores use to
attach non-image files — most commonly PDF downloads — alongside product images. The core
support is intact and the **Filament** admin still exposes it: `ManageMediasRelatedRecords`
iterates `$model->getRegisteredMediaCollections()` and renders one relation-manager tab per
collection, so any collection a definition declares appears with no panel code change
(`packages/admin/src/Support/RelationManagers/MediaRelationManager.php`).

The **Inertia panel** (`packages/panel`) dropped this. Every media surface is hardcoded to
the single `images` collection and to image-only uploads:

- `ProductMediaStoreRequest` (and the Brand / Collection / ProductType equivalents)
  validate `files.*` with the `image` rule.
- The media controllers call `AddsMedia`/`ReordersMedia` without a collection argument, so
  everything lands in `config('lunar.media.collection')` = `'images'`.
- Each edit controller hand-builds a single `media` prop from that one collection
  (`ProductEditController` lines 78-89, duplicated across the four).
- `MediaManager.vue` assumes images throughout: `accept="image/*"`, `<img>` tiles, a hero
  tile, focal point, image-worded i18n keys.

So a store upgrading from v1 with a `downloads` collection sees that collection in Filament
but has no way to view or manage it in the new panel — the collection is invisible, and even
if surfaced, the `image` validation rule would reject a PDF.

The core is already collection-capable: `AddMedia`/`ReorderMedia` take an optional
`$collection`; `HasMedia` exposes `getMediaCollectionTitle()/Description()` per collection
name; the `MediaDefinitions` contract already models multiple named collections. Only the
panel layer has to catch up.

## Proposal

The panel renders **one media section per registered collection** on every media-bearing
edit screen (products, brands, collections, product types), driven by whatever collections
the model's `MediaDefinitions` declares. Image collections keep the existing rich uploader;
non-image collections get a file-list uploader. Groups are declared in code — no admin UI —
matching v1 and the Filament panel.

### Core — collections declare what they accept (`packages/core`)

Both server validation and the image-vs-file UI decision derive from each collection's
declared mime types, keeping the definition class the single source of truth. No change to
the `MediaDefinitions` contract.

- `Media/StandardDefinitions.php`: on the `images` collection, call Spatie's
  `MediaCollection::acceptsMimeTypes([...image types])`. A custom definition adding a
  downloads group declares
  `$model->addMediaCollection('downloads')->acceptsMimeTypes(['application/pdf', ...])`.
- The accepted types are read back off the Spatie `MediaCollection`
  (`$model->getRegisteredMediaCollections()->firstWhere('name', $name)->acceptsMimeTypes`),
  so the panel needs no new contract method to know a group's allowed types.

**Primary / thumbnail scoping.** `HasMedia::thumbnail()` filters only on
`custom_properties->primary = true`, not on collection, and `ReorderMedia` promotes the
first item of whichever collection it reorders to `primary` (and `MediaObserver` promotes
the first upload of an empty collection). Left unscoped, uploading or reordering a document
would make a PDF the model's thumbnail. Both the `primary` promotion and the `thumbnail()`
relation are scoped to the configured image collection (`config('lunar.media.collection')`),
so non-image groups never own primary/thumbnail state. Covered by a core test.

### Panel backend — collection-aware media (`packages/panel`)

The four media controllers, four store requests and four edit-controller media blocks are
near-identical. Factor the shared behaviour once rather than editing each in place.

**`Support/Media/HandlesMedia`** (trait, used by the four `*MediaController`s):

- Resolves the target collection from the request (`collection` field), defaulting to
  `config('lunar.media.collection')`.
- Validates the collection is one the model actually registers
  (`$model->getRegisteredMediaCollections()`), rejecting anything else — a crafted request
  cannot write to an arbitrary collection name.
- Builds the `files.*` rule from that collection's accepted mime types (`mimetypes:...`),
  falling back to no type restriction when the collection declares none, plus the shared
  `max:config('lunar.media.max_upload_kb')`.
- `store`: `$addsMedia->execute($model, $file, $collection)`.
- `reorder`: `$reordersMedia->execute($model, $ids, $collection)`.
- `update` / `destroy`: unchanged — the bound `Media` instance already carries its
  collection.

The per-model `*MediaStoreRequest`s collapse to this shared rule builder; the controllers
keep only their model type and route-name prefix.

**`Support/Media/MediaGroups`** — turns a model into the Inertia prop array, replacing the
inline `->getMedia(...)->map(...)` block duplicated across the four edit controllers. For
each registered collection it emits:

```
{
  collection: string,
  title: string,          // getMediaCollectionTitle()
  description: string,     // getMediaCollectionDescription()
  type: 'image' | 'file',
  accept: string,          // for the file input, from accepted mime types
  items: [...],
  urls: { store, reorder },
}
```

- `type` is `'image'` when the collection is the configured image collection or its accepted
  types are all `image/*`, else `'file'`.
- **Image** items keep the current shape (`id`, `url` via the `small` conversion,
  `original_url`, `name`, `alt`, `caption`, `focal`, `primary`, `update_url`,
  `destroy_url`).
- **File** items instead carry `id`, `file_name`, `mime_type`, `size`, `extension`,
  `original_url` (download), `name`, `caption`, `update_url`, `destroy_url`.

Edit controllers pass `mediaGroups` (replacing the single `media` prop). The per-collection
`store`/`reorder` URLs live inside each group rather than the page-level `urls` map.

**Routes** (`Sections/Catalog/CatalogSection.php` and the brand/collection/product-type
groups): the existing four-route shape (`media.store`, `media.reorder`, `media.update`,
`media.destroy`) is unchanged. The collection travels in the request body of
`store`/`reorder`, so route names stay stable and add-ons anchored on them are unaffected.

### Panel frontend — render per group (`packages/panel/resources/js`)

- **Edit pages** (`pages/{products,brands,collections,product-types}/Edit.vue`): the single
  hardcoded `<MediaManager :items="media" .../>` becomes a loop over `mediaGroups`,
  rendering `<MediaManager>` for `type: 'image'` and the new `<FileManager>` for
  `type: 'file'`. Each group is a sibling `<Section>` card in the main column (pages stack
  sections, they are not tabbed), preserving the current single-images layout when a model
  declares only the images collection.
- **`MediaManager.vue`**: `accept`, the `<Section>` title/description, and the empty-state
  labels come from props (the group) instead of the hardcoded `image/*` and fixed `media.*`
  keys, so it renders any image collection. Hero tile, focal, reorder and the edit dialog
  are unchanged.
- **`FileManager.vue`** (new): a vertical list of file rows — type icon, file name, size,
  download link, rename/edit, delete, drag-to-reorder (reusing the reorder POST + optimistic
  pattern; a list variant of `useGridSort`). `accept` from the group. No hero, focal or crop.
- **Edit dialog**: `MediaEditDialog.vue` is image-specific (focal + crop preview). It is
  branched on group type, or a lighter file-edit dialog (name/caption only) is added for the
  file list. The new component(s) are exported on `resources/js/ui.ts` only if add-ons should
  reuse them directly — optional, since a group declared on an existing model already renders
  automatically.

### Translations

New file-group panel strings (uploader labels, empty state, size hint, download/rename
actions) added to the media lang group, English first, mirrored across all 16 locales. The
`StandardDefinitions` image-collection title key is unchanged; a custom group supplies its
own title/description through its definition (its own lang namespace for add-ons).

### Testing

- **Pest (`tests/panel/`)**: store/reorder reject a collection the model does not register;
  per-group mime enforcement (a PDF rejected by the images group, accepted by a downloads
  group; an image rejected by a PDF-only group); the `mediaGroups` prop shape for a
  multi-collection model (image group vs file group fields, correct per-collection URLs);
  upload/reorder/rename/delete against a non-image group; a document upload/reorder does not
  become the model thumbnail. A test `MediaDefinitions` stub registers `images` plus a
  non-image `downloads` collection (mirroring `tests/admin/Stubs/TestMediaDefinition.php`).
- **Pest (`tests/core/`)**: thumbnail/primary scoping — reordering or uploading to a
  non-image collection leaves `thumbnail()` and image-collection `primary` untouched.
- **Vitest**: `FileManager.test.ts` (upload, reorder, delete, download link, accept); update
  `MediaManager.test.ts` for the prop-driven title/accept.
- `PageScaffoldTest` continues to cover the edit pages. PHPStan + Pint as required.

## Alternatives considered

- **A `{collection}` route segment** instead of a request field: rejected — it multiplies
  route registrations across four models, changes route names (breaking add-on anchors), and
  still needs the same registered-collection validation the body approach applies. The body
  field keeps the four-route shape and names stable.
- **A new `MediaDefinitions` contract method for accepted types / group type**: rejected —
  adding an interface method is a breaking change to a public contract (every downstream
  definition would have to implement it) for information Spatie's `MediaCollection` already
  carries via `acceptsMimeTypes`. Reading it back off the registered collection needs no
  contract change and no Rector rule.
- **One universal media component** that switches on file type internally: rejected — the
  image experience (hero tile, focal point, crop, aspect grid) and the file experience (rows,
  size, download) share almost no layout; a single component would be a tangle of `v-if`. A
  thin `MediaManager` / `FileManager` split with a shared reorder/upload core is clearer.
- **Editing the four controllers / requests / edit blocks in place**: rejected — the logic is
  identical across products, brands, collections and product types; the `HandlesMedia` trait
  and `MediaGroups` builder remove the duplication the current code already carries and make
  a fifth media-bearing model a one-line addition.
- **An admin UI to create groups at runtime**: rejected (and out of scope) — v1 and Filament
  both declare groups in code via `MediaDefinitions`; a runtime UI would need new persistence
  with no existing seam and diverge from both.

## Migration impact

- No database migrations. No changes to the `MediaDefinitions` contract or the media
  actions' signatures (they already accept `$collection`).
- `StandardDefinitions` gains `acceptsMimeTypes` on the images collection — additive; a
  published copy of the definition keeps working (it simply won't type-restrict until
  re-published).
- **Behaviour change:** `HasMedia::thumbnail()` and image `primary` promotion become scoped
  to the configured image collection. For any existing single-collection (`images`) install
  this is inert; it only changes behaviour once a second collection exists — which is the
  point.
- Panel Inertia prop rename: edit controllers emit `mediaGroups` instead of `media`. Internal
  to first-party pages (updated together); not part of the add-on contract surface.
- Translations: media lang group additions across all 16 locales.
- Filament admin: unchanged — it already renders every collection.
- No new PHP or npm dependencies.

## Open questions

- Whether `FileManager` reuses a list variant of `useGridSort` or a simpler list-reorder
  composable — a component-level detail settled in implementation.
- Whether the file-group edit dialog is a branch of `MediaEditDialog` or a separate lighter
  dialog — settled in implementation; behaviour (edit name/caption) is fixed.

## References

- [[0049-inertia-panel]] — panel architecture and the media surface it introduced
- [[0052-panel-brands-section]] — the shared catalog editing surfaces (media, attributes,
  URL slugs) this generalises
- [[0057-panel-products-section]] — the product edit page the primary group renders on
- Filament reference for the target behaviour:
  `packages/admin/src/Support/Resources/Pages/ManageMediasRelatedRecords.php`,
  `packages/admin/src/Support/RelationManagers/MediaRelationManager.php`,
  `tests/admin/Stubs/TestMediaDefinition.php`

## Implementation plan

- [x] Slice 1 — Core: `acceptsMimeTypes` on the `StandardDefinitions` images collection;
      scope `thumbnail()` and `ReorderMedia` primary promotion to the configured image
      collection (`MediaObserver` was already scoped); core tests + the
      `TestMediaGroupDefinitions` stub.
- [x] Slice 2 — Panel backend: `Http/Controllers/Concerns/HandlesMediaReorder` trait +
      `Support/Media/MediaGroups` builder + `Http/Requests/Media/{MediaStoreRequest,MediaUpdateRequest}`
      abstracts; the four `*MediaController`s / `*MediaStoreRequest`s / `*MediaUpdateRequest`s
      collection-aware; edit controllers emit `mediaGroups`; feature tests (collection
      validation, per-group mime enforcement, prop shape, thumbnail scoping).
- [x] Slice 3 — Panel frontend: prop-driven `MediaManager`; `FileManager` + `FileEditDialog`;
      `MediaGroups` wrapper looped on the four edit pages; media lang additions (16 locales);
      `MediaManager`/`FileManager` vitest.
