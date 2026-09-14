<?php

namespace Lunar\SearchRelevance\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Product;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\SearchRelevance\Learning\Overrides;
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

    public function query(Request $request, QueryReport $report, Overrides $overrides, string $query): Response
    {
        $modelTypes = QueryReport::modelTypes();
        $modelType = (string) $request->query('model', $modelTypes->first());
        $params = ['query' => $query, 'model' => $modelType];

        $learned = array_map(fn (array $row) => [
            ...$row,
            '_actions' => ['exclude' => route('panel.search-relevance.exclude', [...$params, 'productId' => $row['product_id']])],
        ], $report->learned($modelType, $query));

        $excluded = $overrides->excluded($modelType, $query);
        $names = Product::query()->whereKey($excluded->all())->get()->keyBy('id');

        return Inertia::render('search-relevance::Query', [
            'query' => $query,
            'model_type' => $modelType,
            'learned' => $learned,
            'variants' => $report->variants($query),
            'excluded' => $excluded->map(fn (int $id) => [
                'product_id' => $id,
                'name' => ($product = $names->get($id)) ? (string) $product->translate('name') : __('search-relevance::panel.query_missing_product', ['id' => $id]),
                'url' => route('panel.search-relevance.include', [...$params, 'productId' => $id]),
            ])->values()->all(),
            'reset_at' => $overrides->resetAt($modelType, $query)?->toIso8601String(),
            'urls' => [
                'index' => route('panel.search-relevance.index'),
                'reset' => route('panel.search-relevance.reset', $params),
            ],
        ]);
    }

    public function product(Product $product, ProductReport $report): JsonResponse
    {
        return response()->json(['queries' => $report->queries($product)]);
    }
}
