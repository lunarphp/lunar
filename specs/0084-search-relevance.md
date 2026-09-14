# 0084 — Search relevance: learned ranking for Lunar search

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-09-14
- TODO item: search relevance layer (query-aware learned ranking, engine-agnostic)
- Target branch: `2.x` (monorepo) and `next` (docs; the v2 pages live under `2.x/` on that branch)
- Prototype: `~/Tmp/search-relevancy` (Laravel app proving the design; see its `RESULTS.md`)

## Problem

Lunar search returns results in the order the engine chooses. Nothing in the stack learns from what shoppers click, add to basket or buy, so a merchant's search never improves with traffic. Clients currently pay Algolia or Loop54 for that learning, at per-record and per-request prices that scale with their success.

The prototype proved a small, engine-agnostic layer on top of Typesense gives a large part of that value:

| Retrieval | Metric | Engine order | Learned order |
|---|---|---|---|
| Keyword only | MRR | 0.339 | 0.426 |
| Hybrid (keyword + vector) | MRR | 0.541 | 0.715 |
| Hybrid | nDCG@10 | 0.726 | 0.755 |

Reranking cost under 1ms per search. Applying learned scores inside the engine (Typesense `_eval`) was measurably worse than reordering in Laravel, and position correction in the scoring job was necessary to stop the ranking learning position bias. This spec turns that prototype into a Lunar package.

Concretely, today:

- `packages/search` engines (`TypesenseEngine`, `MeilisearchEngine`, `DatabaseEngine`) build `SearchResults` inside `get()` with no hook between "engine returned hits" and "storefront renders them".
- Pagination is delegated to Scout per page (`AbstractEngine::getRawResults()` calls `paginateRaw(perPage:)`), so nothing can fetch a wider candidate window and reorder it.
- No search interaction data is captured anywhere in Lunar.

## Proposal

Three deliverables, in order:

1. **Two small hooks in `lunarphp/search`**: a request pipeline and a results pipeline, plus `page()` control on `AbstractEngine`. Generic, usable by any package.
2. **A new package `lunarphp/search-relevance`** (`packages/search-relevance`, namespace `Lunar\SearchRelevance`) that logs searches and events, scores them nightly, reranks results, and ships an Inertia panel section.
3. **A docs PR** to `lunarphp/docs`.

Engine-agnostic by construction: the ranker only needs product ids in engine order, which every engine returns. Hybrid retrieval stays an engine concern (already wired for Typesense in 2.x; Meilisearch needs an external embedder). Nothing engine-specific lives in the relevance package.

### Part 1: hooks in `lunarphp/search`

#### 1.1 Request and results pipelines

Two config-driven pipelines in `packages/search/config/search.php`, mirroring `lunar.cart.pipelines` and `lunar.orders.pipelines`:

```php
'pipelines' => [
    // Run before the engine queries. Stages may change page, perPage, sort, filters.
    'request' => [],
    // Run after SearchResults is built. Stages may reorder, annotate, or replace hits.
    'results' => [],
],
```

Passable objects:

```php
namespace Lunar\Search\Pipelines;

final class SearchRequest
{
    public function __construct(
        public AbstractEngine $engine,
        public int $requestedPage,
        public int $requestedPerPage,
        public array $context = [], // free-form bag for stages to talk to each other
    ) {}
}

final class SearchResponse
{
    public function __construct(
        public SearchRequest $request,
        public SearchResults $results,
    ) {}
}
```

`AbstractEngine` gains:

```php
protected int $page = 1;

public function page(int $page): static;
public function getPage(): int;
public function getPerPage(): int;

/** Extra engine request parameters, merged last. Used by request-pipeline stages (see 1.4). */
public function withParams(array $params): static;
public function getParams(): array;

/** Runs the request pipeline. Called by engines at the top of get(). */
protected function pipeRequest(): SearchRequest;

/** Runs the results pipeline. Called by engines before returning from get(). */
protected function pipeResults(SearchRequest $request, SearchResults $results): SearchResults;
```

`getRawResults()` passes `page: $this->page` to `paginateRaw()`. The three built-in engines call `pipeRequest()` first and wrap their return value in `pipeResults()`. Custom engines opt in by doing the same; existing custom engines keep working untouched (non-breaking).

Pipelines run through `Illuminate\Pipeline\Pipeline`, classes resolved from the container, same as cart pipelines.

#### 1.2 Data object additions

- `SearchHit`: add `public array $meta = []`. Engines populate `meta['score']` where available (Typesense `text_match`, Meilisearch `_rankingScore`, which the engine now requests; Database leaves it unset).
- `SearchResults`: add `public array $meta = []`. Stages annotate (the relevance stage sets `search_id`, `ranking_mode`, `ranking_version`).

Both additions have defaults, so existing `SearchResults::from()` calls are unaffected.

#### 1.3 Tests

`tests/search/` Pest tests: pipeline stages run in config order for each engine; `page()` is honoured by `getRawResults()`; a results stage can reorder hits; `meta` defaults.

#### 1.4 Retrieval baseline fixes (Typesense and Meilisearch)

A client reported partial part numbers returning a few family members plus unrelated products, the same ones every time. The prototype reproduced it against the 2.x engine request and measured the fix (`RESULTS.md`, "Retrieval baseline"): page one for a five-character part-number prefix is 84% unrelated under the current request and 3% after the changes below, with no change to natural-language quality.

Changes in `packages/search`, shipped with 1.1 to 1.3:

- The `exlude_fields` typo in `TypesenseEngine::buildSearch()` the prototype found was already fixed on `2.x` before this spec landed; nothing further to do.
- Add `distance_threshold` to the vector query, configurable as `lunar.search.typesense.vector_distance_threshold`, default `0.6`. Without it the `k: 200` vector query pads every result set, and a nonsense query returns 200 products. Meilisearch counterpart when an embedder is configured (`lunar.search.meilisearch.embedder`): `lunar.search.meilisearch.ranking_score_threshold`.
- Allow request-pipeline stages to override request parameters: `AbstractEngine::withParams(array $params)` merged into the engine request last, for both `TypesenseEngine` and `MeilisearchEngine`. A null value removes the parameter. This is how the relevance package applies part-number retrieval without engine-specific code in the engine itself.
- `lunar:meilisearch:setup` (in `packages/meilisearch`) additionally applies `typoTolerance.disableOnAttributes` for the fields an indexer returns from `getExactMatchFields()` (`ProductIndexer` returns the SKU fields). Today it only sets filterable and sortable attributes.
- `ProductIndexer` already indexes variant SKUs as `skus`. Add `skus_normalised` (uppercased, separators stripped) so `HAGMB` matches `HAG-MB-32A`. In the Typesense collection schema both are `string[]` with `infix: true`, and both must appear in the host app's `scout.typesense.model-settings.<Product>.search-parameters.query_by`. Both live in host config, so the docs must show the exact entries, and the reindex.

The relevance package then ships a `PartNumberRetrieval` request-pipeline stage: when `QueryNormaliser::isPartNumber()` is true (one token mixing letters and digits, not a bare unit like `20mm`), it restricts retrieval to the SKU fields and removes typo tolerance and semantic search. Part-number queries also skip stemming in the normaliser. The stage maps to each engine through `withParams()`:

| | Typesense | Meilisearch |
|---|---|---|
| Restrict to SKU fields | `query_by: skus,skus_normalised` | `attributesToSearchOn: ['skus', 'skus_normalised']` |
| Partial match | `prefix: true`, `infix: always` | Prefix on the last word is always on. No infix: `MB32A` will not match `HAG-MB-32A`. Document as a known limitation. |
| No typos | `num_typos: 0` | Index setting `typoTolerance.disableOnAttributes: ['skus', 'skus_normalised']`, applied by `lunar:meilisearch:setup` |
| Keep every token | `drop_tokens_threshold: 0` | `matchingStrategy: all` |
| No semantic padding | omit `vector_query` | Not applicable unless an embedder is configured; then `hybrid.semanticRatio: 0` |

**How much of the client's problem applies to Meilisearch.** Measured with the same query sets against Meilisearch 1.24 (`RESULTS.md`, "Meilisearch"). Of the three causes found on Typesense, only typo tolerance applies. Lunar's `MeilisearchEngine` sends no vector query unless an embedder is configured, so there is no semantic padding, and Meilisearch prefix-matches the last query word by default, so recall of every SKU family was already complete. Typo tolerance is the defect: an eight-character code returns 6.8 unrelated products per page under default settings and none once typos are disabled on the SKU attributes. That is an index setting, so `lunar:meilisearch:setup` must apply it; it is not something a request stage can do.

Two Meilisearch behaviours to document rather than fix:

- No infix matching. A fragment from inside a code (`MB32A` for `HAG-MB-32A`) returns nothing on Meilisearch and the full family on Typesense. Clients whose customers search mid-code fragments need Typesense.
- Page padding. Meilisearch's default `last` matching strategy lists every full match first and then fills the page with documents matching fewer terms. For letter-only prefixes such as `HAG-MB`, which the classifier treats as text because they contain no digit, page one is the whole family followed by partial matches. Not a ranking error, but a storefront that shows result counts will show inflated totals.

**Classifier rule, both engines**: a part number is one token, letters and digits mixed, hyphens, dots and slashes allowed, and not a bare unit like `20mm`. Letter-only codes are treated as text on purpose; widening the rule would send words like `cable-gland` to SKU-only retrieval.

### Part 2: `lunarphp/search-relevance`

#### 2.1 Package skeleton

```
packages/search-relevance/
  composer.json               name lunarphp/search-relevance, requires lunarphp/search; lunarphp/panel is optional
  config/search-relevance.php merged as lunar.search_relevance
  database/migrations/
  resources/js/               panel add-on (Vue), built like packages/panel-addon-example
  resources/lang/en/
  resources/views/            Blade components for storefront tracking
  routes/storefront.php       events endpoint
  src/
    SearchRelevanceServiceProvider.php
    Settings.php               mode persisted by the panel, falling back to config
    Contracts/{QueryNormaliser,Signal,Ranker,ScoreAggregator}.php
    Data/{RankingContext,Hit,HitCollection}.php
    Normalisers/DefaultQueryNormaliser.php
    Signals/{SignalCombiner,QueryAffinitySignal}.php
    Rankers/{BucketedRanker,NullRanker}.php
    Pipelines/{PartNumberRetrieval,WidenRequest,RankResults}.php
    Logging/{SearchLogger,LogSearch (job)}.php
    Events/{RecordEvent (job),Attribution.php}
    Http/Controllers/SearchEventController.php
    Listeners/{AttributeCartLine,AttributeOrderLines}.php
    Scoring/{MySqlScoreAggregator,PostgresScoreAggregator,PhpScoreAggregator}.php
    Console/{ScoreCommand,ReplayCommand,PruneCommand}.php
    Models/{SearchQuery,SearchEvent,SearchQueryScore}.php
    Panel/{SearchRelevanceSection.php, Widgets/SearchConversionWidget.php}
    RetrievalVersion.php
```

Ported from the prototype (`app/Relevance/*`, `app/Search/SearchService.php`, `app/Console/Commands/RelevanceScore.php`) with namespaces changed. The simulator, evaluator, holdout, LTR and native mode are not ported.

#### 2.2 Configuration (`lunar.search_relevance`)

```php
return [
    'mode' => env('LUNAR_SEARCH_RELEVANCE_MODE', 'shadow'), // off | shadow | on
    'models' => [Product::class],        // which searchable models are ranked
    'window' => 250,                     // candidate window fetched from the engine
    'bucket_size' => 10,                 // reorder only within buckets of this many hits
    'cache_ttl' => 300,
    'learned_union' => ['max' => 5, 'min_relative' => 0.1],
    'impressions_logged' => 50,
    'attribution_ttl_minutes' => 30,
    'session_key' => 'cart',             // cart | session: what identifies a shopper

    'normaliser' => DefaultQueryNormaliser::class,
    'normaliser_version' => 1,           // bump when normalisation rules change

    'ranker' => BucketedRanker::class,
    'signals' => [
        QueryAffinitySignal::class => 1.0,
    ],

    'scoring' => [
        'weights' => ['click' => 1, 'basket' => 3, 'purchase' => 5],
        'position_eta' => 0.7,
        'max_position_weight' => 5,
        'half_life_days' => 30,
        'window_days' => 180,
        'min_sessions' => 3,
        'max_products_per_query' => 50,
        'schedule' => '02:00',
    ],

    'retention_days' => 400,             // raw queries and events pruned after this

    'guards' => [
        'events_rate_limit' => '60,1',   // per minute per shopper
        'max_searches_per_minute' => 30, // sessions above this are ignored by scoring
    ],
];
```

Modes:

- `off`: nothing is logged or ranked.
- `shadow`: everything is logged, the reranked order is computed and stored alongside the shown order, the engine order is displayed. Default after install, so training data accrues from day one and `replay` can prove uplift before switching on.
- `on`: reranked order is displayed.

The panel persists a mode override in `{prefix}search_relevance_settings`; `Lunar\SearchRelevance\Settings::mode()` returns the override when present and the config value otherwise. Every code path reads the mode through `Settings`, never `config()` directly.

#### 2.3 Database

Four tables, prefixed via `Lunar\Core\Database\Migration::$prefix`. All types chosen to work on MySQL 8 and Postgres. No partitioning (a `PruneCommand` replaces it).

**`{prefix}search_queries`**

| Column | Type | Notes |
|---|---|---|
| id | ulid PK | the `search_id` |
| model_type | string | searchable model class |
| raw_query | text | |
| normalised_query | string(255) | indexed |
| filters_hash | char(32) | md5 of filters + facets |
| session_id | string(64) | indexed |
| customer_id | bigint null | Lunar customer if known |
| version | string(32) | indexed, see RetrievalVersion |
| mode | string(8) | off, shadow, on |
| result_count | int | |
| shown | json | first N product ids in displayed order |
| ranked | json null | reranked order when it differs (shadow and on) |
| features | json null | per-impression request-time features, reserved for LTR |
| created_at | timestamp | indexed |

**`{prefix}search_events`**

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| search_id | ulid FK | |
| product_id | bigint | product level, not variant |
| position | smallint | 1-based, as displayed |
| type | string(16) | click, basket, purchase |
| source | string(16) | organic, learned, explore |
| session_id | string(64) | |
| created_at | timestamp | index (created_at, search_id) |

**`{prefix}search_query_scores`**

| Column | Type | Notes |
|---|---|---|
| model_type | string | |
| normalised_query | string(255) | |
| product_id | bigint | |
| score | double | |
| relative | double | 0..1 |
| sessions | int | |
| version | string(32) | |
| updated_at | timestamp | |

Primary key `(model_type, normalised_query, product_id)`. Index on `(version, normalised_query)`.

**`{prefix}search_relevance_settings`**: `key` string PK, `value` json, timestamps. Holds the panel's mode override.

Keep `normalised_query` at 255 so it can be indexed on MySQL without prefix indexes. Truncate longer queries at log time.

#### 2.4 Contracts and data objects

```php
namespace Lunar\SearchRelevance\Contracts;

interface QueryNormaliser
{
    public function normalise(string $query): string;

    /** One token mixing letters and digits, not a bare unit such as 20mm. Drives PartNumberRetrieval. */
    public function isPartNumber(string $query): bool;
}

interface Signal
{
    /** @return array<int, float> product_id => 0..1 */
    public function scores(RankingContext $context, array $productIds): array;
}

interface Ranker
{
    public function rank(RankingContext $context, HitCollection $hits): HitCollection;
}

interface ScoreAggregator
{
    /** Upserts search_query_scores for the given version and returns rows written. */
    public function aggregate(string $version, array $config): int;
}
```

`RankingContext`: `modelType`, `normalisedQuery`, `sessionId`, `customerId`, `mode`, `sort`, `filtersHash`, `version`. `shouldRank()` is false when the query is empty or `*`, an explicit sort is set, or mode is `off`.

`Hit`: `productId`, `originalPosition`, `score` (engine score or 0), `document`, `source`, mutable `boost`. Serialises to array for the cache; never cache the objects themselves (prototype hit `__PHP_Incomplete_Class` across CLI and FPM).

`HitCollection extends Illuminate\Support\Collection` with `productIds()`.

All four contracts are bound in the service provider from config so a host app or client package can replace any of them.

#### 2.5 Pipeline stages

Request pipeline order: `PartNumberRetrieval`, then `WidenRequest`. Results pipeline: `RankResults`. The package registers all three into `lunar.search.pipelines` from its service provider; hosts can reorder or remove them in config.

**`PartNumberRetrieval`** (request pipeline). When `QueryNormaliser::isPartNumber()` is true, calls `withParams()` with the engine-specific parameters from the table in 1.4. Part-number searches are still logged, but `WidenRequest` skips ranking for them because per-code queries are too sparse to learn from.

**`WidenRequest`** (request pipeline). If ranking applies (`models` contains the engine's model, mode is not `off`, `shouldRank()`), records `requestedPage`/`requestedPerPage` in `context`, then sets `page(1)` and `perPage(window)` on the engine when `requestedPage * requestedPerPage <= window`. Beyond the window it leaves the request alone. Also builds and stores the `RankingContext` in `context`.

**`RankResults`** (results pipeline). If `WidenRequest` widened the request:

1. Build `HitCollection` from `results->hits`, read the cached window if present (key: model, version, mode, normalised query, filters hash, customer id).
2. Union learned products the engine did not return: fetch by ids through the engine (`filter` on id, `perPage` = count), insert at the head of the second bucket, cap by `learned_union`.
3. `Ranker::rank()`.
4. Cache the ranked window as arrays for `cache_ttl`.
5. Slice to `requestedPage`/`requestedPerPage`, rebuild `SearchResults` with correct `page`, `perPage`, `totalPages`, `links`. Facets pass through unchanged.
6. In `shadow` mode, display the original order but keep the ranked order for logging.
7. Set `results->meta`: `search_id`, `ranking_mode`, `ranking_version`. Set each `hit->meta`: `position`, `original_position`, `boost`, `source`.
8. Dispatch `LogSearch` (queued) with the query, shown order, ranked order, impressions and features.

Beyond the window, stages pass through but the search is still logged.

A `SearchResults` produced this way is what the storefront already consumes, so existing storefront code needs no change to keep working. It needs one change to start learning: rendering the tracking attributes.

#### 2.6 Storefront tracking

Shopper identity: the Lunar cart session identifier when `session_key` is `cart` (it survives login and is what basket and order lines already relate to), else the Laravel session id.

Blade storefronts:

```blade
{{-- once per results page, outputs the beacon script and CSRF wiring --}}
<x-lunar-search-relevance::tracking :results="$results" />

{{-- on each result --}}
<div {{ lunar_search_attrs($results, $hit) }}> ... </div>
```

`lunar_search_attrs()` renders `data-lunar-search-id`, `data-lunar-product-id`, `data-lunar-position`, `data-lunar-source`. The script sends `navigator.sendBeacon()` to `POST /lunar/search/events` on click of any element inside a tracked node that navigates.

Headless and Inertia storefronts: the same endpoint accepts JSON. `SearchResults->meta['search_id']` and `hit->meta` are in the API payload.

#### 2.6.1 Storefront client (`@lunarphp/search-relevance`)

The Blade component must not be the only packaged implementation, because the Lunar storefront starter kit is Inertia and Vue. One client, published to npm from `packages/search-relevance/resources/client`, serves both:

- Framework-agnostic entry `@lunarphp/search-relevance`: `sendSearchEvent(payload, options)` (`navigator.sendBeacon` with a keepalive `fetch` fallback, CSRF token read from the page or passed in, optional `session_id`), `eventFor()` / `trackHit()` built from the results and hit `meta`, `trackingAttributes()` for the `data-lunar-*` markup, and `attachSearchTracking()` for delegated click tracking.
- Vue entry `@lunarphp/search-relevance/vue`: `useSearchTracking(results, options)` returning `track(hit)`, `attrs(hit)` and `send(payload)`, plus a `v-lunar-search-hit` directive.
- An IIFE build (`dist/tracking.iife.js`, `window.LunarSearchRelevance`) that the Blade tracking component inlines, so Blade and headless storefronts run the same code.
- `SearchHit::$meta` and `SearchResults::$meta` carry `LiteralTypeScriptType` shapes so the generated `Lunar.Search` types describe `search_id`, `position`, `source` and friends.

`dist/` is tracked because the Composer package reads the IIFE at render time. The package is added to the npm workspace, the drift check, the publish workflow and the panel JS CI job (vitest, type-check, build).

Endpoint validation: `search_id` exists, `product_id` is in that search's `shown` list, `position` in range, rate limited per shopper. Everything else is rejected with 204 so bots learn nothing.

#### 2.7 Basket and purchase attribution

- A click stores `attribution.{product_id} => {search_id, position, source, expires}` in the session for `attribution_ttl_minutes`.
- `AttributeCartLine` is an Eloquent `created` observer on `Lunar\Core\Models\CartLine`. It resolves the line's product (purchasable to product), looks up attribution, and if present writes `meta['search_attribution']` on the line and dispatches a `basket` event.
- `AttributeOrderLines` listens to `Lunar\Core\Events\Orders\OrderPlaced`. For every order line whose `meta` carries `search_attribution`, dispatch a `purchase` event. `Lunar\Core\Pipelines\Order\Creation\CreateOrderLines` already copies cart line `meta` to the order line (verified on 2.x), so no order pipeline step is needed.

All writes are queued; nothing touches the request path.

#### 2.8 Scoring

`php artisan lunar:search-relevance:score`, scheduled daily at `scoring.schedule` via `callAfterResolving(Schedule::class)` as `LunarServiceProvider` does.

The aggregation is one query on MySQL 8 and Postgres:

```sql
WITH raw AS (
    SELECT q.model_type, q.normalised_query, e.product_id,
        SUM(weight(e.type)
            * CASE WHEN e.source = 'explore' THEN 1 ELSE LEAST(POWER(e.position, :eta), :max_pos) END
            * POWER(0.5, {age_seconds} / 86400 / :half_life)) AS score,
        COUNT(DISTINCT e.session_id) AS sessions
    FROM search_events e JOIN search_queries q ON q.id = e.search_id
    WHERE e.created_at > :window_start AND q.version = :version
    GROUP BY q.model_type, q.normalised_query, e.product_id
    HAVING COUNT(DISTINCT e.session_id) >= :min_sessions
),
ranked AS (
    SELECT *, score / MAX(score) OVER (PARTITION BY model_type, normalised_query) AS relative,
           ROW_NUMBER() OVER (PARTITION BY model_type, normalised_query ORDER BY score DESC) AS rn
    FROM raw
)
SELECT ... FROM ranked WHERE rn <= :max_products
```

Driver differences are limited to `{age_seconds}`: `EXTRACT(EPOCH FROM now() - e.created_at)` on Postgres, `TIMESTAMPDIFF(SECOND, e.created_at, NOW())` on MySQL. `weight()` is a `CASE` with bound values. `:window_start` is computed in PHP so `make_interval` is not needed. Rows are written with the query builder's `upsert()`, which works on both drivers. Afterwards delete rows for the current version with `updated_at` older than the job start, and all rows with other versions.

`PhpScoreAggregator` does the same in chunked PHP for SQLite so the package test suite can run locally without a server database; CI runs the SQL aggregators on the existing mysql/pgsql matrix.

Guards applied inside the aggregation: sessions exceeding `max_searches_per_minute` are excluded.

`RetrievalVersion::current()` = `n{normaliser_version}:{scout driver}:{hybrid?}`. `QueryAffinitySignal` reads only rows with the current version, so a normaliser or engine change relearns cleanly instead of applying stale scores (the prototype measured stale scores making results worse).

`lunar:search-relevance:prune` deletes queries and events older than `retention_days`. Scheduled weekly.

#### 2.9 Replay (the proof)

`php artisan lunar:search-relevance:replay --days=30`

For every logged search in the period that led to a purchase, compare the purchased product's position in `shown` versus `ranked`. Print mean reciprocal rank for both, the number of searches, and the share where the reranked order placed the purchased product higher. This is the number a client sees before `mode` is switched to `on`, and the same table drives the panel's "Uplift" card.

#### 2.10 Panel section (Inertia)

Built as an add-on to `lunarphp/panel`, following `packages/panel-addon-example`: a `Section` registered with `Panel::section()`, Vue pages registered via `window.LunarPanel.registerPages()`, own Vite build under `vendor/lunar-panel/search-relevance`, `lang` namespace `search-relevance`. No Filament work.

`SearchRelevanceSection`:

- **Permission** `search:manage-relevance`, seeded by the package migration the same way first-party handles are.
- **Navigation**: group `search`, item "Search relevance", icon from the panel's built-in set.
- **Routes** (all under `can:search:manage-relevance`):
  - `panel.search-relevance.index`: KPIs for a date range (searches, click-through rate, search conversion rate, zero-result rate, mean click position) plus the replay uplift card. Top queries table with counts and conversion, zero-result queries table, queries with no clicks (merchandising opportunities).
  - `panel.search-relevance.query`: one normalised query. Learned products in score order with the explainability breakdown per product: relative score, clicks, baskets, purchases, distinct sessions, last event, and the engine position it typically comes from. Raw query variants that normalise to it.
  - `panel.settings.search-relevance.index`: mode switch (off/shadow/on) with a confirmation when switching to `on`, and read-only display of the weights and version. Mode persists in `{prefix}search_relevance_settings` through `Settings`.
- **Dashboard widget** `SearchConversionWidget` (`WidgetSpan::Half`): searches and conversion for the dashboard range.
- **Slot** on `products.edit:content:after` showing "Search performance" for the product (queries it wins, clicks, purchases).
- **Global search source**: normalised queries, so staff can jump to a query page from the palette.

Vue pages use the panel's existing table and card components; nothing custom beyond a small bar for relative score. Ship `en` translations only; other locales fall back per the panel's namespace rules.

#### 2.10.1 Abuse and manipulation guards

Learned ranking is a feedback loop, so bots and bad actors can try to feed it. The design limits the blast radius structurally (the bucketed ranker only reorders within buckets of ten and the learned union lands at position 11 or later, so nothing can be pushed into the top ten unless the engine already put it there) and adds these guards:

- **Event validation**: an event needs a real `search_id`, a product from that search's shown list, a position inside it, and must arrive within `guards.event_window_minutes` of the search (default 120). The events table is unique on `(search_id, product_id, type)`, so replaying a click adds nothing.
- **Scoring dedupe**: each session contributes at most one event of each type per query and product; a session cannot vote twice by re-running the search.
- **Trusted sessions**: with `guards.trusted_sessions_only` (default on), only sessions that hold a cart or belong to a known customer count. A bot minting fresh sessions gains nothing; it must interact with the storefront to get a cart, and even then one vote per session.
- **Crawlers** are neither logged nor ranked (`guards.ignored_user_agents`), so reporting stays honest and the tables stay small. Sessionless headless clients are kept, since they identify the shopper explicitly on the events endpoint.
- **Refunds and cancellations** remove the purchase events their attributed order lines produced (`ForgetPurchases` on `OrderCancelled` and `OrderRefunded`).
- **Staff overrides** in the panel query page, stored in `{prefix}search_learning_overrides`: exclude a product from learning for a query (its score is removed immediately and future events ignored, reversible) and reset learning for a query (discards everything learned and ignores events before the reset). Both go through `Lunar\SearchRelevance\Learning\Overrides`, which both aggregators honour.

Not done on purpose: storing IP addresses for abuse analysis. The per-IP rate limit on the events endpoint covers the crude case without the privacy obligations.

#### 2.11 Tests

`tests/search-relevance/` (Pest, per monorepo layout):

- Unit: normaliser cases (units, part numbers, punctuation, plurals), signal combination and clamping, bucket ordering including the learned-union insertion point, `RetrievalVersion`.
- Feature: request and results pipelines widen and slice correctly across the window boundary using the Database engine and a fake for Typesense/Meilisearch; shadow mode displays engine order but logs ranked order; events endpoint rejects unknown search ids, products not shown, and out-of-range positions; cart line and order attribution end to end; scoring on MySQL and Postgres in CI (`cross-db` group), PHP aggregator on SQLite; prune; replay output.
- Panel: routes gated by permission; index and query pages render with fixture data; settings update.

## Alternatives considered

- **Inside `lunarphp/search`.** Rejected: brings four tables, a scheduler, queue jobs and a panel section into a package every search user installs. Optional package with two generic hooks is cleaner and keeps `search` small.
- **Engine-native boosting** (Typesense `sort_by=_eval`, Meilisearch ranking rules). Rejected on evidence: prototype MRR 0.390 versus 0.715 for Laravel-side reranking under hybrid retrieval, because a `sort_by` discards rank fusion and Typesense buckets by score range rather than count. Also not portable across engines.
- **Learning-to-rank model now.** Prototype showed +0.03 nDCG over the lookup table and no meaningful cold-start gain, at the cost of a Python training sidecar. Deferred. The `features` column keeps the door open.
- **Postgres-only with partitioning.** Rejected: most Lunar installs run MySQL. Replaced by a retention prune and driver-specific age expressions.
- **Separate impressions table.** Rejected for write volume (50 rows per search). A JSON `shown` column on the query row is enough for validation and replay.
- **Do nothing.** Clients keep paying per-request search bills for learning Lunar could provide at fixed cost.

## Migration impact

- **Database**: four new tables from `search-relevance`. The `search` package adds nothing to the database.
- **Public contract surface**: `AbstractEngine` gains `page()`, `getPage()`, `getPerPage()`, `withParams()`, `getParams()`, `pipeRequest()`, `pipeResults()`; `SearchHit` and `SearchResults` gain `meta` with defaults; `ScoutIndexer` gains `getExactMatchFields()`. Non-breaking. Custom engines that do not call the pipeline methods keep their current behaviour and do not get ranking.
- **Upgrade path for v1.x**: none planned. The package targets 2.x only. `lunarphp/upgrade` needs no change.
- **Translation / locale impact**: new `search-relevance` lang namespace, `en` shipped; 15 other locales fall back. Storefront components carry no translatable text.
- **Filament / admin impact**: none. Panel section is Inertia only. Filament users get logging, scoring and ranking but no admin screens.
- **Queues**: the package assumes a queue worker. With the `sync` driver it still works, with writes on the request path. Documented.
- **Cache**: window cache uses the default store. Arrays only, so any driver works.

## Docs PR (`lunarphp/docs`)

New and changed pages, each added to `docs.json` under the v2.x navigation:

1. **`2.x/addons/search-relevance.mdx`** (Add-ons, General, after `search`). Sections: part-number search and how queries are classified; what it does and what it does not (retrieval stays with the engine); installation; modes and the shadow-first rollout; storefront tracking for Blade and headless with the exact attributes and payload; attribution and what cart line `meta` contains; scoring explained in plain language with the config table; versioning and why changing the normaliser relearns; scheduling and queues; the replay command with sample output; supported engines table (Typesense, Meilisearch, Database, custom engines that opt into the pipelines).
2. **`2.x/addons/search.mdx`**: new "Pipelines" section documenting `lunar.search.pipelines.request` and `results`, the passable objects, `page()`, and the `meta` fields, with a short custom-stage example.
3. **`2.x/extending/search.mdx`**: new "Search relevance" section with examples of a custom `QueryNormaliser` (client-specific synonyms), a custom `Signal` (an example using stock level), and swapping the `Ranker`. State clearly that the same normaliser must be used at log time and search time.
4. **`2.x/admin/search-relevance.mdx`** (Admin Panel tab): the section's pages, the permission handle, reading the explainability breakdown, when to switch from shadow to on.
5. **`2.x/guides/search.mdx`**: add a short "Make search learn" subsection linking to the add-on and showing the two storefront lines.
6. **`logs/flight-plan.mdx`**: entry for the feature.

Every class, config key and command in the docs must be verified against the monorepo `2.x` branch before merge, per the docs repo's `CLAUDE.md`. Code samples use full namespaces (`Lunar\SearchRelevance\...`).

## Resolved questions

1. **Where does the panel persist the mode switch?** A `{prefix}search_relevance_settings` key-value table read through `Lunar\SearchRelevance\Settings`. Config remains the fallback and the only option without the panel.
2. **Shopper identity for headless storefronts.** The events endpoint accepts an explicit `session_id` for API clients without a cart session cookie; the storefront passes its cart identifier. The Blade component and the default headless payload omit it and let the server resolve it.
3. **Product-level versus variant-level events.** Product ids only. A nullable `variant_id` can be added later without breaking the tables.
4. **Meilisearch `showRankingScore`.** Requested by the engine as part of Part 1, so `hit->meta['score']` is populated on Meilisearch too.
5. **Rate limiting key.** Per shopper id and per IP, whichever trips first.
6. **Zero-result behaviour for nonsense queries.** Left to the storefront. The guide recommends a zero-results state rather than the engine padding the page; the add-on ships no fallback stage.

## References

- Prototype results: `~/Tmp/search-relevancy/RESULTS.md`
- [[0040-storefront-context]] for the session and customer resolution the logger reuses
- [[0049-inertia-admin-panel]] and `packages/panel-addon-example` for the panel add-on pattern

## Implementation plan

- [x] Slice 1 — `search`: pipelines, `page()`, `withParams()`, `meta` fields, retrieval baseline fixes, tests
- [x] Slice 2 — `search-relevance`: skeleton, migrations, config, normaliser, logging, events endpoint, storefront tracking, attribution, prune
- [x] Slice 3 — `search-relevance`: scoring aggregators, schedule, `RetrievalVersion`, replay command
- [x] Slice 4 — `search-relevance`: contracts, `QueryAffinitySignal`, `BucketedRanker`, `WidenRequest`, `RankResults`, learned union, shadow logging
- [x] Slice 5 — Panel section, widget, product slot, search source, settings
- [x] Slice 6 — Docs PR (`lunarphp/docs`)
- [x] Slice 8 — Abuse guards: event window and dedupe, trusted sessions, crawler skip, refund/cancel forgetting, panel exclusions and reset
- [x] Slice 7 — Storefront client `@lunarphp/search-relevance` (framework-agnostic + Vue), shared with the Blade component, typed `meta`
- [ ] Follow-ups (separate specs): `AccountHistorySignal`, exploration strip
