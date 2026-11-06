<?php

namespace Lunar\SearchRelevance\Panel\Reports;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

/**
 * Headline search metrics for a window. Rates are percentages of searches in
 * the window; events are attributed to the search they belong to, so a click
 * after the window still counts toward the search that produced it.
 */
class SearchKpis
{
    /**
     * @return array{searches: int, click_through_rate: float, conversion_rate: float, zero_result_rate: float, mean_click_position: float|null}
     */
    public function forWindow(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $searches = $this->searches($start, $end)->count();
        $zeroResults = $this->searches($start, $end)->where('result_count', 0)->count();

        $clicked = $this->events($start, $end)->where('type', 'click')->distinct()->count('search_id');
        $purchased = $this->events($start, $end)->where('type', 'purchase')->distinct()->count('search_id');
        $meanPosition = $this->events($start, $end)->where('type', 'click')->avg('position');

        return [
            'searches' => $searches,
            'click_through_rate' => $this->rate($clicked, $searches),
            'conversion_rate' => $this->rate($purchased, $searches),
            'zero_result_rate' => $this->rate($zeroResults, $searches),
            'mean_click_position' => $meanPosition === null ? null : round((float) $meanPosition, 1),
        ];
    }

    /** @return Builder<SearchQuery> */
    public function searches(DateTimeInterface $start, DateTimeInterface $end): Builder
    {
        return SearchQuery::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);
    }

    /**
     * Events joined to their search, filtered by the search's own timestamp.
     *
     * @return Builder<SearchEvent>
     */
    public function events(DateTimeInterface $start, DateTimeInterface $end): Builder
    {
        $events = (new SearchEvent)->getTable();
        $queries = (new SearchQuery)->getTable();

        return SearchEvent::query()
            ->join($queries, "{$queries}.id", '=', "{$events}.search_id")
            ->where("{$queries}.created_at", '>=', $start)
            ->where("{$queries}.created_at", '<', $end);
    }

    public function rate(int $part, int $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }
}
