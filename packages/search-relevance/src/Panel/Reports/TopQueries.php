<?php

namespace Lunar\SearchRelevance\Panel\Reports;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

/** The per-query tables on the index page, all grouped by normalised query. */
class TopQueries
{
    public function __construct(protected SearchKpis $kpis) {}

    /**
     * @return array<int, array{query: string, searches: int, clicks: int, conversions: int, conversion_rate: float, url: string}>
     */
    public function top(DateTimeInterface $start, DateTimeInterface $end, int $limit = 20): array
    {
        $counts = $this->grouped($this->kpis->searches($start, $end), $limit);

        if ($counts->isEmpty()) {
            return [];
        }

        $events = (new SearchEvent)->getTable();
        $queries = (new SearchQuery)->getTable();

        $engagement = $this->kpis->events($start, $end)
            ->whereIn("{$queries}.normalised_query", $counts->keys()->all())
            ->groupBy("{$queries}.normalised_query")
            ->selectRaw("{$queries}.normalised_query as normalised_query")
            ->selectRaw("sum(case when {$events}.type = ? then 1 else 0 end) as clicks", ['click'])
            ->selectRaw("count(distinct case when {$events}.type = ? then {$events}.search_id end) as conversions", ['purchase'])
            ->get()
            ->keyBy('normalised_query');

        return $counts->map(function (int $searches, string $query) use ($engagement): array {
            $row = $engagement->get($query);
            $conversions = (int) ($row?->conversions ?? 0);

            return [
                'query' => $query,
                'searches' => $searches,
                'clicks' => (int) ($row?->clicks ?? 0),
                'conversions' => $conversions,
                'conversion_rate' => $this->kpis->rate($conversions, $searches),
                'url' => $this->url($query),
            ];
        })->values()->all();
    }

    /** @return array<int, array{query: string, searches: int, url: string}> */
    public function zeroResult(DateTimeInterface $start, DateTimeInterface $end, int $limit = 20): array
    {
        return $this->simple($this->kpis->searches($start, $end)->where('result_count', 0), $limit);
    }

    /**
     * Queries that returned results but were never clicked in the window.
     *
     * @return array<int, array{query: string, searches: int, url: string}>
     */
    public function noClick(DateTimeInterface $start, DateTimeInterface $end, int $limit = 20): array
    {
        $queries = (new SearchQuery)->getTable();

        $clicked = $this->kpis->events($start, $end)
            ->where('type', 'click')
            ->select("{$queries}.normalised_query");

        return $this->simple(
            $this->kpis->searches($start, $end)->where('result_count', '>', 0)->whereNotIn('normalised_query', $clicked),
            $limit,
        );
    }

    /**
     * @param  Builder<SearchQuery>  $searches
     * @return array<int, array{query: string, searches: int, url: string}>
     */
    protected function simple(Builder $searches, int $limit): array
    {
        return $this->grouped($searches, $limit)
            ->map(fn (int $count, string $query): array => ['query' => $query, 'searches' => $count, 'url' => $this->url($query)])
            ->values()
            ->all();
    }

    /**
     * @param  Builder<SearchQuery>  $searches
     * @return Collection<string, int> normalised query => search count, most searched first
     */
    protected function grouped(Builder $searches, int $limit): Collection
    {
        return $searches
            ->groupBy('normalised_query')
            ->selectRaw('normalised_query, count(*) as searches')
            ->orderByDesc('searches')
            ->orderBy('normalised_query')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (SearchQuery $row) => [(string) $row->normalised_query => (int) $row->searches]);
    }

    public function url(string $query): string
    {
        return route('panel.search-relevance.query', ['query' => $query]);
    }
}
