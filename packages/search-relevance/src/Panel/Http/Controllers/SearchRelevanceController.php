<?php

namespace Lunar\SearchRelevance\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Product;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\SearchRelevance\Panel\Reports\ProductReport;
use Lunar\SearchRelevance\Panel\Reports\QueryReport;
use Lunar\SearchRelevance\Panel\Reports\SearchKpis;
use Lunar\SearchRelevance\Panel\Reports\TopQueries;
use Lunar\SearchRelevance\Scoring\Replay;

class SearchRelevanceController
{
    public function index(Request $request, SearchKpis $kpis, TopQueries $topQueries, Replay $replay): Response
    {
        $range = DashboardRange::fromValue($request->query('range'));
        $uplift = $replay->run($range->start());

        return Inertia::render('search-relevance::Index', [
            'range' => $range->value,
            'ranges' => array_map(fn (DashboardRange $case) => [
                'value' => $case->value,
                'label' => __("panel::dashboard.range_{$case->value}"),
            ], DashboardRange::cases()),
            'kpis' => $kpis->forWindow($range->start(), $range->end()),
            'uplift' => [
                'searches' => $uplift->searches,
                'mrr_shown' => round($uplift->mrrShown, 3),
                'mrr_ranked' => round($uplift->mrrRanked, 3),
                'improved_share' => round($uplift->improvedShare * 100, 1),
                'worsened_share' => round($uplift->worsenedShare * 100, 1),
            ],
            'top_queries' => $topQueries->top($range->start(), $range->end()),
            'zero_result_queries' => $topQueries->zeroResult($range->start(), $range->end()),
            'no_click_queries' => $topQueries->noClick($range->start(), $range->end()),
            'urls' => ['index' => route('panel.search-relevance.index')],
        ]);
    }

    public function query(Request $request, QueryReport $report, string $query): Response
    {
        $modelTypes = QueryReport::modelTypes();
        $modelType = (string) $request->query('model', $modelTypes->first());

        return Inertia::render('search-relevance::Query', [
            'query' => $query,
            'model_type' => $modelType,
            'learned' => $report->learned($modelType, $query),
            'variants' => $report->variants($query),
            'urls' => ['index' => route('panel.search-relevance.index')],
        ]);
    }

    public function product(Product $product, ProductReport $report): JsonResponse
    {
        return response()->json(['queries' => $report->queries($product)]);
    }
}
