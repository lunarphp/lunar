<?php

namespace Lunar\SearchRelevance\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\SearchRelevance\Learning\Overrides;
use Lunar\SearchRelevance\Panel\Reports\QueryReport;

/** Staff levers against manipulated or embarrassing learned results. */
class OverridesController
{
    public function exclude(Request $request, Overrides $overrides, string $query, int $productId): RedirectResponse
    {
        $overrides->exclude($this->modelType($request), $query, $productId);

        return back()->with('success', __('search-relevance::panel.override_excluded'));
    }

    public function include(Request $request, Overrides $overrides, string $query, int $productId): RedirectResponse
    {
        $overrides->include($this->modelType($request), $query, $productId);

        return back()->with('success', __('search-relevance::panel.override_included'));
    }

    public function reset(Request $request, Overrides $overrides, string $query): RedirectResponse
    {
        $overrides->reset($this->modelType($request), $query);

        return back()->with('success', __('search-relevance::panel.override_reset_done'));
    }

    protected function modelType(Request $request): string
    {
        $modelTypes = QueryReport::modelTypes();
        $modelType = (string) $request->input('model', $modelTypes->first());

        return $modelTypes->contains($modelType) ? $modelType : (string) $modelTypes->first();
    }
}
