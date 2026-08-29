# 0074 — Panel global search (command palette)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-08-29
- TODO item: Panel global search — Cmd+K palette across orders, customers, products, collections, brands; extensible by add-ons (spec 0074)

## Problem

The sidebar search button in the panel is a stub. `NavBody.vue` renders a button with a `Cmd+K` keycap hint but no click handler, and no `Cmd+K` binding exists anywhere — spec 0049 explicitly deferred global search and the command palette out of the initial panel slice.

Staff who know what they are looking for (an order reference, a customer name, a SKU) have to navigate to the right index page first and use its per-page search. There is no single place to jump straight to a record, and add-ons that introduce their own entities (blog posts, reviews, …) have no way to make them findable at all.

## Proposal

A command palette opened by clicking the sidebar search button or pressing `Cmd+K` / `Ctrl+K`. Its primary job is record search across registered entity sources; it also lists quick actions (create verbs) and, before the user types, the staff member's recently viewed records.

### UX

- Opens as a centred dialog (existing `Dialog.vue` / reka-ui primitives; `TargetPickerDialog.vue` is the closest analogue) with a single text input focused on open.
- Empty state (no query): a "Recently viewed" group of the last records this staff member opened, followed by a "Quick actions" group.
- Typing (debounced 250 ms, via `lib/http.ts`) fetches record results from one endpoint and fuzzy-filters the quick actions client-side. Results render grouped by source ("Orders", "Products", …) with an icon, a primary label, and a secondary hint line.
- Kind-filter chips above the results narrow the fetch to selected sources (`kinds[]`), same interaction as the discount target picker.
- Full keyboard support: arrows to move, Enter to visit the highlighted row (`router.visit`), Esc to close. The palette closes on navigation.
- The sidebar button keeps its current styling; it gains a click handler and the placeholder comment goes.

### Search endpoint

`GET {panel}/search` → `panel.search`, registered in `packages/panel/routes/web.php` inside the authenticated `lunar.panel` group (no route-level permission — gating is per source, below). JSON, not Inertia.

- Params: `q` (required, non-empty), `kinds[]` (optional narrowing).
- Fans out across the sources the user is allowed to see, each contributing at most `PER_SOURCE = 5` rows so no source crowds out the rest — the shape `DiscountTargetSearchController` already established.
- Response rows are uniform: `{kind, kind_label, id, label, hint, url, icon}`.

### Matching

Two paths, chosen per source at query time:

- **SQL `LIKE` (the default, no dependencies).** The term is split into tokens and every token must match one of the source's clauses, so word order and partial words are forgiven ("friday black", "smith 104"). Genuine misspellings are not — `LIKE` has no fuzziness.
- **Scout opt-in, for typo tolerance.** A `lunar.panel.search.scout_enabled` config flag (default `false`, mirroring the Filament admin's `lunar.filament.scout_enabled`). When enabled and a source's model uses the core `Searchable` concern, the term runs through Scout first and the resulting keys constrain the source's base query (`whereIn` + `orderBySequence`) — the exact mechanism the Filament admin's `RecordSearch` uses. Sources whose model is not indexed fall back to `LIKE` individually, so mixed setups keep working.

Typo tolerance itself comes from the engine: Meilisearch tolerates one typo on words of five-plus characters and two on nine-plus out of the box, Typesense similarly. A store on Scout's `database` driver gets no fuzziness from the Scout path — the honest framing is "typo-tolerant when the store runs a search engine, substring matching otherwise". All five built-in entities are already Scout-indexed via the indexers in `lunar.search.indexers`, so a store with an engine configured gets typo tolerance with one config flag.

### Search sources

New abstract `Lunar\Panel\Search\SearchSource`:

```php
abstract class SearchSource
{
    abstract public function key(): string;                 // 'products'

    abstract public function label(): string;               // translated group heading

    /** Base query — model, eager loads, scopes. Both matching paths start here. */
    abstract public function query(): Builder;

    /** LIKE constraints for one token; the resolver ANDs tokens and applies the limit. */
    abstract public function applyTerm(Builder $query, string $token): void;

    /** @return array{id: int|string, label: string, hint: ?string, url: string} */
    abstract public function row(Model $model): array;

    /** Gate handle, e.g. 'catalog:manage-products'; null is visible to every panel user. */
    public function permission(): ?string { return null; }

    public function icon(): string { return 'search'; }

    public function position(): Position { /* Position::last() default */ }
}
```

Sources are instantiated through the container and filtered per user by a `SearchSourceResolver` (mirroring `PageActionResolver`), ordered via the shared `Position` / `OrderResolver` machinery. The resolver also owns matching-path selection — tokenised `LIKE` versus Scout keys — so a source only declares its base query and its `LIKE` clauses and gets the Scout path for free when its model is indexed. It exposes `kinds()` for the palette's filter chips and `rowFor()` for the recently-viewed prop. A consumer that wants to widen a built-in source's matching rebinds its class in the container.

Registration follows the established `Section` seam — a new method aggregated by `PanelManager::processSections()` into the resolver:

```php
/** @return array<int, class-string<SearchSource>> */
public function searchSources(): array { return []; }
```

`SectionExtension` carries the same two methods, so an extension can contribute search to a section it does not own.

Built-in sources, registered by the sections that own the entities. The "Matches" column lists the `LIKE`-path clauses, aligned with what each index page already searches; under the Scout path the indexed fields come from the model's indexer in `lunar.search.indexers`:

| Source | Permission | Matches | Destination |
| --- | --- | --- | --- |
| Orders | `sales:manage-orders` | reference, customer reference, billing name/company/email/postcode | `panel.orders.show` |
| Customers | `sales:manage-customers` | first/last/company name, tax identifier | `panel.customers.edit` |
| Products | `catalog:manage-products` | name (locale map), variant SKU, URL slug | `panel.products.edit` |
| Collections | `catalog:manage-collections` | name | `panel.collections.edit` |
| Brands | `catalog:manage-products` | name, handle, URL slug | `panel.brands.edit` |

Hints carry the disambiguating detail per source (order: customer name + total; product: SKU or status; customer: company; etc.).

### Quick actions

New abstract `Lunar\Panel\Search\SearchCommand` — a static command the palette can run, which for v1 means navigate:

```php
abstract class SearchCommand
{
    abstract public function key(): string;        // 'products.create'

    abstract public function label(): string;      // translated, e.g. 'Create product'

    abstract public function url(): string;

    abstract public function permission(): string;

    public function icon(): string { return 'plus'; }

    public function position(): Position { /* priority default */ }
}
```

Registered via a matching `Section::searchCommands()` seam. Built-ins cover the create flows the panel already ships: product, collection, brand, customer, discount, product type.

Commands are resolved per user once per request and shared as an Inertia prop by `HandlePanelInertiaRequests` (they are needed before any fetch, for the empty state), so the palette filters them client-side against the translated label with no round-trip.

### Recently viewed

Client-side only — no database table.

- `HandlePanelInertiaRequests` shares a `visitedRecord` prop built from the record already bound to the route (the same one page actions resolve), shaped by `SearchSourceResolver::rowFor()` — the record is refetched through its source's own query so `row()` sees the relations it eager loads. Record pages need no changes, the shape is identical to endpoint rows by construction, and an add-on that registers a source gets recently-viewed with no opt-in.
- `PanelLayout.vue` watches the prop on navigation and records it to `localStorage` under a per-staff key, deduped by `kind + id`, most recent first, capped at 8.
- The palette reads the list on open for the empty-state group. A recent entry whose record has since been deleted 404s on visit; the visit's `onHttpException` prunes it.

### Frontend

- `components/CommandPalette.vue` — the dialog, input, chips, grouped result list. Built on `Dialog.vue` plus reka-ui listbox primitives; mounted once in `PanelLayout.vue`.
- `composables/useCommandPalette.ts` — module-scoped open state so `NavBody.vue` (and anything else) can open it without prop drilling.
- `Cmd+K` / `Ctrl+K` handler added to the existing `keydown` listener in `PanelLayout.vue`, alongside the sidebar-collapse binding.
- The palette is internal machinery — not exported from `ui.ts`. Add-ons extend search purely through the PHP seams above.

### Permissions

Every source and command declares the same gate handle that guards its section's routes and navigation, and the resolvers filter by `$user->can()` — the endpoint can never return rows the user could not reach through the nav. `tests/panel/Feature/AuthorizationTest.php` gains coverage for the endpoint.

### Translations

New `search.php` lang group in the panel package across all 16 locales: input placeholder, empty/no-results copy, group headings ("Recently viewed", "Quick actions"), source labels, and command labels. Values translated per locale, not mirrored from English.

### Testing

- Pest (panel suite): endpoint feature tests — permission filtering, `kinds[]` narrowing, per-source limits, row shape, tokenised term matching per source; Scout-path tests against Scout's `collection` driver (flag on → keys constrain the query and order results; unindexed model → per-source `LIKE` fallback); unit tests for resolver ordering and Section aggregation; add-on registration exercised via the section fixtures used by existing registry tests.
- Vitest: palette open/close and keybinding, debounce + fetch, keyboard navigation, quick-action filtering, recently-viewed storage (cap, dedupe, per-staff key).
- `panel-addon-example` gains an example source and command, keeping the add-on surface exercised end-to-end.

## Alternatives considered

- **SQL `LIKE` only, no Scout path** — zero dependencies, but no answer to misspelled queries, which are routine when staff half-remember a customer or product name. Rejected in favour of the opt-in above.
- **Scout required** — best relevance, but it would make the panel's headline search depend on the store having Scout configured, which nothing else in the panel does. Rejected; the opt-in gets the same result for stores that want it.
- **Database-native fuzzy matching** (`SOUNDEX`, `pg_trgm`, `LEVENSHTEIN`) — per-database syntax in a cross-database package, slow without dedicated indexes, mediocre results. Rejected.
- **Client-side fan-out to the existing typeahead endpoints** — N requests per keystroke, three different response shapes, and permission logic duplicated in JS. Rejected.
- **Porting the Filament `GlobalSearchDescriptor` directly** — the descriptor idea survives (per-entity class: query + row + url), but the wiring is panel-native (Section seam, `Position`, container resolution) rather than Filament's resource traits.
- **Page navigation as a result group** — matching nav items ("Settings > Taxes") was considered and dropped for v1; the sidebar already does that job and the palette stays focused on records and verbs.

## Migration impact

- No database changes.
- Public surface: additive only — new `Section::searchSources()` / `Section::searchCommands()` methods with empty defaults, two new abstracts, one new endpoint, one new config key (`lunar.panel.search.scout_enabled`, default `false`). No breaking changes; nothing for the upgrade package.
- Translations: one new lang group in 16 locales.
- Filament admin: untouched; its global search (spec 0009) remains as is.

## Open questions

- Should `TableExtension::searchQuery()` widening also feed the matching source, so an add-on that widens an index search widens the palette for free? Deferred — sources are their own seam for v1; revisit if add-ons end up duplicating clauses.
- ~~Small-screen presentation.~~ Resolved: the dialog is `w-[calc(100vw-2rem)]` up to a 560px cap, anchored below the top edge, so it fits narrow viewports without a separate layout.

## References

- `specs/0049-inertia-panel.md` — deferred global search / command palette out of the initial panel scope.
- `specs/completed/0009-filament-actions-and-global-search.md` — the Filament admin's descriptor-based global search.
- `packages/panel/src/Http/Controllers/Discounts/DiscountTargetSearchController.php` — the multi-kind fan-out endpoint pattern (per-kind limits, uniform rows, `kinds[]` narrowing).
- `packages/panel/resources/js/components/TargetPickerDialog.vue` — the dialog + debounced-search interaction the palette generalises.

## Implementation plan

- [x] Slice 1 — backend: `SearchSource` abstract, resolver (tokenised `LIKE` path + Scout opt-in path), `Section::searchSources()` + `PanelManager` wiring, `scout_enabled` config key, five built-in sources, `panel.search` endpoint, feature tests.
- [x] Slice 2 — frontend: `CommandPalette.vue`, `useCommandPalette`, Cmd+K binding, sidebar button wiring, kind chips, `search` lang group (16 locales), vitest coverage.
- [x] Slice 3 — quick actions: `SearchCommand` abstract, `Section::searchCommands()`, built-in create commands, shared Inertia prop, palette group.
- [x] Slice 4 — recently viewed: `visitedRecord` shared prop via `rowFor()`, localStorage composable, empty-state group, 404 pruning.
- [x] Slice 5 — add-on surface: example source + command in `panel-addon-example`, add-on docs.
