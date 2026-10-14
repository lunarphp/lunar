<?php

namespace Lunar\SearchRelevance\Panel\Reports;

use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\RetrievalVersion;

/** The queries a product is found through, for the product report page and the edit sidebar card. */
class ProductReport
{
    public function __construct(protected RetrievalVersion $version) {}

    /**
     * @return array<int, array{query: string, relative: float|null, clicks: int, baskets: int, purchases: int, url: string}>
     */
    public function queries(Product $product, int $limit = 20): array
    {
        $events = (new SearchEvent)->getTable();
        $queries = (new SearchQuery)->getTable();

        $rows = SearchEvent::query()
            ->join($queries, "{$queries}.id", '=', "{$events}.search_id")
            ->where("{$events}.product_id", $product->getKey())
            ->groupBy("{$queries}.normalised_query")
            ->selectRaw("{$queries}.normalised_query as normalised_query")
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as clicks", ['click'])
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as baskets", ['basket'])
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as purchases", ['purchase'])
            ->orderByDesc('purchases')
            ->orderByDesc('clicks')
            ->orderBy('normalised_query')
            ->limit($limit)
            ->get();

        $learned = SearchQueryScore::query()
            ->where('model_type', $product::class)
            ->where('version', $this->version->current($product::class))
            ->where('product_id', $product->getKey())
            ->whereIn('normalised_query', $rows->pluck('normalised_query')->all())
            ->get()
            ->keyBy('normalised_query');

        return $rows->map(function (SearchEvent $row) use ($learned): array {
            $query = (string) $row->normalised_query;
            $score = $learned->get($query);

            return [
                'query' => $query,
                'relative' => $score ? round((float) $score->relative, 3) : null,
                'clicks' => (int) $row->clicks,
                'baskets' => (int) $row->baskets,
                'purchases' => (int) $row->purchases,
                'url' => route('panel.search-relevance.query', ['query' => $query]),
            ];
        })->all();
    }
}
