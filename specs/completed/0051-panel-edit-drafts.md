# 0051 — Panel edit drafts and field-level conflict detection

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-17
- TODO item: Panel edit drafts — autosaved pending edits with field-level conflict detection (spec 0051)

## Problem

Panel edit pages lose work and overwrite work.

- **Lost work**: an edit form (`customers/Edit.vue`, `settings/channels/Edit.vue`) holds
  state only in an Inertia `useForm`. Navigating away, a session timeout, or a browser
  crash discards everything typed since the last save. There is no dirty-state guard and
  no persistence of in-progress edits.
- **Silent overwrites**: saves are last-write-wins. Two staff members editing the same
  customer both submit the full field set; whoever saves second reverts the other's
  changes without either noticing. Nothing in core or the panel detects concurrent
  edits — no version column, no `updated_at` comparison, no lock (the activity log
  records what happened, after the fact).

Row-level optimistic locking (reject the save if `updated_at` moved) would fix the
overwrite but at the wrong granularity: two staff editing *different* fields on the same
record would block each other for no reason.

## Proposal

A drafts layer in `lunarphp/panel`: in-progress edits autosave to an `edit_drafts` row
(one per staff member per record), and commits detect conflicts **per field** by
comparing each drafted field's base snapshot against the current database value. Staff
editing disjoint fields never collide; staff editing the same field get a scoped
resolution UI instead of a silent overwrite.

The feature lives entirely in the panel package. Core stays headless — drafts exist only
because of admin editing, so core gains no awareness of them; the `edit_drafts` table
references core models purely through its polymorphic relation. The Filament admin is
untouched (a Livewire-native equivalent can be built later if wanted; see Alternatives).

First wiring target: the customer details form. Its field set (scalar columns plus the
relation-backed `customer_group_ids` array) exercises both value shapes the design must
handle, without the multi-form complexity of the addresses/users tabs. Channels and
future sections follow by registering a definition — no new infrastructure.

### Data model

New table `edit_drafts` (standard `lunar.database.table_prefix`), the panel package's
first migration (`packages/panel/database/migrations/`, extending
`Lunar\Core\Database\Migration` for the prefix, loaded via `loadMigrationsFrom` as the
stripe and opayo packages do):

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `draftable_type` | string | morph alias via `Base::morphName()` (e.g. `customer`) |
| `draftable_id` | unsignedBigInteger | all core models use bigint PKs |
| `staff_id` | foreignId | constrained to the prefixed `staff` table, `cascadeOnDelete` |
| `data` | json | changed fields only, keyed by field key: `{"first_name": "Glenn"}` |
| `base_snapshot` | json | DB value of each drafted field at the moment it was first touched |
| `created_at` / `updated_at` | timestamps | `updated_at` doubles as the staleness clock |

Unique index on `(draftable_type, draftable_id, staff_id)` — one draft per staff member
per record. Model: `Lunar\Panel\Models\EditDraft` with a `draftable()` morphTo and a
`staff()` belongsTo. The owner column is `staff_id`, not `user_id`: the panel
authenticates `Lunar\Core\Models\Staff` via the staff guard, and storefront users never
touch drafts.

#### The base snapshot

When a field first enters `data`, its current database value is captured into
`base_snapshot` under the same key. Subsequent edits to that field update `data` only —
the snapshot stays fixed until commit or discard. Commit-time conflict detection is then
a per-key comparison: `base_snapshot[key]` versus the current DB value. Match means no
one else changed the field since the draft started tracking it; mismatch means someone
did. No audit trail, version columns, or core changes required.

#### Field keys and value normalisation

A draft's `data`/`base_snapshot` keys are **field keys** — stable, dot-notation
identifiers for independently-conflictable units of a form:

- Scalar columns: the column name (`first_name`, `account_ref`).
- Translated JSON columns (products later): one key per locale — `name.en`, `name.fr` —
  so two staff translating into different languages never conflict.
- Attribute data (products later): `attribute_data.{handle}`, comparing the raw
  persisted value (`FieldType::jsonSerialize()`), since FieldType instances are not
  meaningfully `==`-comparable but their raw values are.
- Relation-backed form fields: a resource-defined key resolving to a normalised value —
  `customer_group_ids` resolves to a sorted integer array of the current pivot ids.

Values are normalised before both storage and comparison: JSON encoding with recursively
sorted object keys, resource-defined per-field normalisation (empty string to null for
nullable text columns, sorted unique ids for relation arrays). Comparison is equality of
the normalised encodings. Related *rows* (prices, addresses) are not fields — they are
separate editing surfaces out of scope here (see Out of scope).

### Service layer

New `Lunar\Panel\Drafts\` namespace, following the monorepo service-layer rules
(constructor-promoted DI, interfaces in `Contracts/`, no facades-for-forwarding):

- **`Contracts\DraftableResource`** — a per-resource definition, the seam that makes
  drafts generic:
  - `model(): string` — the draftable model class.
  - `fields(): array` — the allowed field keys; autosave rejects keys outside this set.
  - `currentValues(Model $record): array` — current normalised DB value per field key
    (this is where `customer_group_ids` reads the pivot).
  - `rules(Model $record): array` — validation rules for a full commit payload (the
    customer definition reuses `CustomerRequest::rules()`).
  - `commit(Model $record, array $values): void` — applies resolved values through the
    existing core action contracts (`UpdatesCustomer` for customers); the panel never
    writes model fields directly.
  - `labels(): array` — field key to lang key, for the conflict dialog.
- **`Contracts\DraftManager`** / **`Drafts\DraftManager`** — the operations, each taking
  the record, the staff member, and the registered definition:
  - `merge($record, $staff, array $data): EditDraft` — upsert the draft; replace `data`
    wholesale with the incoming diff, capture `base_snapshot` entries for newly-present
    keys via `currentValues()`, drop snapshot entries for keys no longer present. An
    empty diff deletes the row (the form returned to clean).
  - `discard($record, $staff): void` — delete the row.
  - `commit($record, $staff, array $data, array $rebase = []): CommitResult` — merge
    `$data` first (the client's final un-debounced diff), apply any `$rebase` snapshot
    updates (see the resolution protocol), validate the merged full payload against
    `rules()`, detect conflicts, and either apply everything through
    `DraftableResource::commit()` and delete the draft, or return the conflict set
    having applied **nothing**.
- **Registry**: sections declare their draftable resources through a new
  `Section::draftables(): array` hook (same optional-hook pattern as `tableExtensions()`
  and `pageActions()`); `PanelManager` indexes them by morph alias. Registering a
  definition also attaches a `deleted` listener to the model class that removes its
  drafts, so drafts never point at gone records.

#### Commit and conflict detection

1. Merge the request's `data` diff into the draft (capturing snapshots for new keys).
2. Validate the full payload — the record's `currentValues()` overlaid with the draft's
   `data` — against `rules()`. Failures return 422 before any conflict work.
3. For each key in `data`, compare `base_snapshot[key]` to the current DB value.
4. **No conflicts**: apply the full value set via `DraftableResource::commit()` inside a
   transaction, delete the draft, flash success into the session, return 200.
5. **Any conflict**: apply nothing. Return 409 with, per conflicting field:
   `{key, label, mine, base, theirs}` — the drafted value, the value the draft started
   from, and the current DB value.

Commits are **atomic**: a commit with conflicts applies no fields, including
non-conflicting ones. The requirements sketch proposed applying clean fields
immediately and 409-ing only the rest; that leaves the record in a half-intended state
(change `first_name` + `last_name`, conflict on `last_name`, and the record briefly
carries a mismatched name), splits one logical save into two writes with two activity
entries, and means validation ran against a payload that is then only partially applied.
Resolution is an immediate, in-dialog step — holding the clean fields in the draft for
the seconds it takes costs nothing. Flagged in Open questions since it deviates from the
initial requirements.

The disjoint-fields case still works exactly as intended: staff A drafting `first_name`
and staff B drafting `customer_group_ids` both commit cleanly, in either order, with no
conflict shown to anyone. Only same-field edits collide.

#### Resolution protocol

On 409 the client shows the conflict dialog; the user resolves each field (keep mine /
take theirs / manual merge). Re-commit sends the resolved values in `data` plus
`rebase: {key: theirs}` — for each resolved key, the **current-DB value the user was
shown**. The server sets `base_snapshot[key]` to the rebase value, then runs the normal
commit flow. If the DB moved *again* between showing the dialog and re-committing, the
rebase value no longer matches and the field conflicts again — the user never silently
overwrites a version they did not see. (Rebasing to "current value at re-commit time"
instead would be a time-of-check race; the client must pin the version it resolved
against.)

### HTTP layer

Three routes per resource, registered inside the owning section's route closure next to
the existing CRUD (inheriting auth middleware and the section's `can:` permission — for
customers, `sales:manage-customers`), all handled by one shared
`Http\Controllers\EditDraftController` that resolves the `DraftableResource` from the
bound model's morph alias:

| Method | Route | Purpose |
|---|---|---|
| `PATCH` | `customers/{customer}/draft` | `DraftManager::merge()`; returns `{data, updated_at}` |
| `DELETE` | `customers/{customer}/draft` | discard |
| `POST` | `customers/{customer}/draft/commit` | commit; 200 / 409 conflicts / 422 validation |

There is no `GET /draft`: the edit page is an Inertia visit, so the edit controller
includes a `draft` prop (`{data, updated_at}` or `null`) resolved for the current staff
member. Only the draft owner is ever addressed — the routes operate on "the current
user's draft for this record", so no cross-staff access exists to authorise.

Commit is `POST` rather than the sketch's `PUT`: it is not idempotent (it mutates the
record, deletes the draft, and writes activity/search side effects).

Drafts are invisible to other staff until committed: no "someone has unsaved changes"
indicator, no table badge, no locking. Conflicts surface exactly once, at commit, scoped
to the fields that actually collided.

### Inertia panel UI

The panel currently performs every request through Inertia — deliberately unsuitable
here, since Inertia's cycle is page-visit-shaped and its error contract is validation
errors, not a 409 conflict payload. Autosave and commit therefore go through a new
panel-owned fetch helper; everything else on the page stays Inertia.

- **`resources/js/lib/http.ts`** — a thin wrapper over native `fetch` (no new npm
  dependency): same-origin JSON requests, `X-XSRF-TOKEN` from the cookie, throws typed
  `DraftConflictError` (carrying the 409 conflict set) and `ValidationError` (422
  errors bag). This becomes the panel's sanctioned non-Inertia transport, exported on
  `ui.ts` for add-ons with the same need.
- **`resources/js/composables/useEditDraft.ts`** — the reusable form driver:
  - Input: pristine server values, the `draft` prop, and the three endpoint URLs.
  - Exposes reactive `values` (pristine overlaid with the draft), `isDirty`, autosave
    state (`saving` / `savedAt`), `errors`, `conflicts`, and `commit()` / `discard()` /
    `resolve()`.
  - Autosave: a debounced (~750 ms) watcher diffs `values` against pristine and
    `PATCH`es the diff; a diff shrinking to empty issues the `DELETE`. In-flight
    autosaves are serialised so a stale response never clobbers newer state.
  - `commit()` sends the current diff (not waiting out the debounce), on success clears
    local draft state and `router.reload({preserveScroll: true})` — the endpoint
    flashed success into the session, so the existing server-flash `FlashMessage`
    displays it with no new toast mechanism. On `DraftConflictError` it populates
    `conflicts`; on `ValidationError`, `errors` (rendered inline exactly as
    `form.errors` is today).
- **`resources/js/components/DraftConflictDialog.vue`** — a `Dialog`-based component
  following the panel's `v-model:open` overlay convention. One row per conflicted
  field: the translated label, "your value" and "current value" side by side (the base
  value shown subtly for context), a keep-mine / take-theirs choice, and an editable
  value input for manual merges. Confirming emits the resolutions; the composable
  re-commits with the rebase payload. Cancelling leaves the draft intact.
- **`customers/Edit.vue`** — the details form moves from `useForm` to `useEditDraft`.
  When the page hydrates from an existing draft, a banner shows "restored unsaved
  changes from {time}" with a discard action. Save commits. The notes, addresses, and
  users forms keep their existing direct Inertia flows.
- **Exports**: `useEditDraft`, `DraftConflictDialog`, and the http helper join `ui.ts`
  (and the mirrored `@lunarphp/panel` index), so add-on edit pages get drafts by
  registering a `DraftableResource` and consuming the same composable.
- **Translations**: new `drafts.php` lang group (banner, autosave states, dialog copy,
  flash messages, conflict labels) in `en` first, mirrored across all 16 locales.

### Expiry

Drafts untouched for 7 days (configurable, `lunar.panel.drafts.ttl_days`) are pruned:
`EditDraft` implements `MassPrunable` with a `prunable()` scope on `updated_at`, and
`PanelServiceProvider` schedules the prune daily via `callAfterResolving(Schedule::class)`
— the same pattern core uses for `lunar:prune:carts`. A week-old base snapshot is stale
enough that its conflict comparisons stop being trustworthy; pruning turns "resume a
dead draft and fight phantom conflicts" into "start fresh".

## Alternatives considered

- **Row-level optimistic locking** (version column or `updated_at` check) — rejected:
  blocks disjoint-field edits, the common concurrent case in a back office, and offers
  no resolution path beyond "reload and retype".
- **Partial apply on conflict** (the requirements sketch: apply clean fields, 409 the
  rest) — rejected for atomic commit; reasoning inline above. Revisit if real usage
  shows long-lived conflict-resolution sessions where holding clean fields hurts.
- **Drafts in core** — rejected: core must stay headless; drafts exist only because of
  admin editing. The polymorphic relation gives the panel everything it needs without
  core awareness. If Filament later wants equivalent behaviour, build a Livewire-native
  version or extract a shared service once this pattern is proven.
- **axios for the non-Inertia calls** — rejected: the panel has no HTTP client today
  and the need (JSON, CSRF header, typed 409/422) is a few dozen lines over native
  `fetch`. Adding a runtime dependency to the panel bundle — and implicitly blessing it
  for every add-on — is not warranted.
- **Reusing Inertia partial visits for autosave** — rejected per the requirements:
  autosave must not enter the page-visit cycle (scroll/focus/history side effects), and
  commit's 409 payload has no home in Inertia's error contract.
- **A "someone is editing" presence indicator** — deferred: it needs its own freshness
  mechanism (polling or sockets) and reintroduces soft-lock social pressure the
  field-level design exists to avoid. The conflict dialog already covers the case where
  it matters.
- **Full-snapshot drafts** (store every field, diff at commit) — rejected: diffs keep
  payloads small and, more importantly, scope conflict detection to fields the user
  actually touched — a full snapshot would flag conflicts on fields they never edited.

## Migration impact

- **Database**: one new table, `edit_drafts`, as the panel package's first migration
  (panel-owned directory, not the core baseline — the table is a panel feature, and
  non-core packages shipping their own migrations has precedent in stripe/opayo). No
  changes to core tables.
- **Breaking changes**: none. New surface only.
- **Upgrade path**: none required; v2 is unreleased and v1 has no equivalent feature.
- **Translations**: new `drafts.php` group across all 16 panel locales.
- **Filament / admin impact**: none. Both panels continue to share the staff guard; a
  Filament user and a panel drafter editing the same record are exactly the
  concurrent-edit case the commit check catches.
- **Public contract surface** (treated as contract from first release): the
  `DraftableResource` and `DraftManager` contracts, `Section::draftables()`, the
  `EditDraft` model and table, the three endpoint shapes and their 409/422 payloads,
  the rebase protocol, the `draft` Inertia prop shape, and the `ui.ts` exports
  (`useEditDraft`, `DraftConflictDialog`, the http helper). Config:
  `lunar.panel.drafts.ttl_days`.

## Open questions

- ~~Atomic vs partial-apply commit~~ — resolved: implemented atomic (apply nothing when
  any field conflicts), deviating from the initial requirements' partial apply for the
  reasons argued inline.
- ~~Should the customer notes sidebar form join the draft scope~~ — resolved: stays a
  direct save; it is a separate endpoint and low-contention.
- ~~Host-app models outside the core morph map~~ — resolved: FQCN morph aliases in
  `draftable_type` are acceptable; registration does not require an alias.
- ~~Multi-tab behaviour for the same staff member~~ — resolved: last-write-wins on the
  single draft row (the unique index makes it safe); commit overlay-merges rather than
  replacing, so a commit never drops fields another tab drafted. A same-user cross-tab
  merge UI is deliberately out of scope.

## References

- Initial requirements: "Lunar v2: Pending Edit Drafts & Field-Level Conflict Detection"
  (this spec supersedes it; deviations — staff ownership, atomic commit, fetch over
  axios, POST commit — are called out inline).
- [[0049-inertia-panel]] — panel architecture, section hooks, `ui.ts` add-on surface.
- [[0019-attribute-system-redesign]] / [[0018-dedicated-name-description-fields]] — the
  value shapes behind the field-key scheme.
- [[0016-service-layer-di]] — action/contract conventions the commit path follows.
- Prior art: Shopify admin's unsaved-changes bar (draft persistence UX); git's
  three-way merge (base/mine/theirs resolution model).

## Implementation plan

- [x] Slice 1 — Persistence + service layer: `edit_drafts` migration, `EditDraft` model
  (`MassPrunable` + scheduled prune), `DraftableResource` / `DraftManager` contracts and
  implementation (merge/discard/commit, snapshot capture, normalisation, conflict
  detection, rebase), `Section::draftables()` registry + deleted-record cleanup; unit
  tests for the conflict matrix.
- [x] Slice 2 — HTTP layer: shared `EditDraftController`, the three customer routes,
  `CustomerDraftResource` (fields, group-ids resolver, `CustomerRequest` rules,
  `UpdatesCustomer` commit), `draft` prop on the customer edit response, session flash
  on commit; feature tests covering autosave lifecycle, 409/422/200 paths, rebase
  re-commit, ownership, and pruning.
- [x] Slice 3 — Frontend driver: `lib/http.ts` fetch wrapper with typed errors,
  `useEditDraft` composable (debounced diff autosave, serialised in-flight requests,
  commit/discard/resolve); vitest coverage.
- [x] Slice 4 — UI wiring: `DraftConflictDialog`, customer `Edit.vue` on `useEditDraft`
  with the restored-draft banner, `drafts.php` lang group across 16 locales, `ui.ts` /
  `@lunarphp/panel` exports; Inertia page tests and a vitest pass over the dialog.
