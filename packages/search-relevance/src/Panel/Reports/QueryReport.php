<?php

namespace Lunar\SearchRelevance\Panel\Reports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\RetrievalVersion;

/** One normalised query: its learned products with the explainability breakdown, and its raw variants. */
class QueryReport
{
    public function __construct(protected RetrievalVersion $version) {}

    /**
     * @return array<int, array{product_id: int, name: string, relative: float, score: float, clicks: int, baskets: int, purchases: int, sessions: int, last_event_at: string|null, typical_position: float|null, url: string|null}>
     */
    public function learned(string $modelType, string $query): array
    {
        $scores = SearchQueryScore::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->where('version', $this->version->current($modelType))
            ->orderByDesc('relative')
            ->orderBy('product_id')
            ->get();

        if ($scores->isEmpty()) {
            return [];
        }

        $ids = $scores->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $engagement = $this->engagement($modelType, $query)->whereIn('product_id', $ids)->get()->keyBy('product_id');
        $products = Product::query()->whereIn('id', $ids)->get()->keyBy('id');

        return $scores->map(function (SearchQueryScore $score) use ($engagement, $products): array {
            $productId = (int) $score->product_id;
            $row = $engagement->get($productId);
            $product = $products->get($productId);
            $typical = $row?->typical_position;

            return [
                'product_id' => $productId,
                'name' => $product ? (string) $product->translate('name') : __('search-relevance::panel.query.missing_product', ['id' => $productId]),
                'relative' => round((float) $score->relative, 3),
                'score' => round((float) $score->score, 3),
                'clicks' => (int) ($row?->clicks ?? 0),
                'baskets' => (int) ($row?->baskets ?? 0),
                'purchases' => (int) ($row?->purchases ?? 0),
                'sessions' => (int) $score->sessions,
                'last_event_at' => $row?->last_event_at ? (string) $row->last_event_at : null,
                'typical_position' => $typical === null ? null : round((float) $typical, 1),
                'url' => $product ? route('panel.products.edit', $product) : null,
            ];
        })->values()->all();
    }

    /** @return array<int, array{raw_query: string, searches: int}> */
    public function variants(string $query): array
    {
        return SearchQuery::query()
            ->where('normalised_query', $query)
            ->groupBy('raw_query')
            ->selectRaw('raw_query, count(*) as searches')
            ->orderByDesc('searches')
            ->orderBy('raw_query')
            ->limit(50)
            ->get()
            ->map(fn (SearchQuery $row): array => ['raw_query' => (string) $row->raw_query, 'searches' => (int) $row->searches])
            ->all();
    }

    /**
     * Event counts per product for the query, from every logged search of it.
     *
     * @return Builder<SearchEvent>
     */
    protected function engagement(string $modelType, string $query)
    {
        $events = (new SearchEvent)->getTable();
        $queries = (new SearchQuery)->getTable();

        return SearchEvent::query()
            ->join($queries, "{$queries}.id", '=', "{$events}.search_id")
            ->where("{$queries}.model_type", $modelType)
            ->where("{$queries}.normalised_query", $query)
            ->groupBy("{$events}.product_id")
            ->selectRaw("{$events}.product_id as product_id")
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as clicks", ['click'])
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as baskets", ['basket'])
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as purchases", ['purchase'])
            ->selectRaw("max({$events}.created_at) as last_event_at")
            ->selectRaw("avg({$events}.position) as typical_position");
    }

    /** @return Collection<int, string> */
    public static function modelTypes(): Collection
    {
        return collect(config('lunar.search_relevance.models', [Product::class]));
    }
}
