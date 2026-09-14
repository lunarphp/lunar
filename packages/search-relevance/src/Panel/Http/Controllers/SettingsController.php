<?php

namespace Lunar\SearchRelevance\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\SearchRelevance\Panel\Reports\QueryReport;
use Lunar\SearchRelevance\RetrievalVersion;
use Lunar\SearchRelevance\Settings;

class SettingsController
{
    public function index(Settings $settings, RetrievalVersion $version): Response
    {
        return Inertia::render('search-relevance::Settings/Index', [
            'mode' => $settings->mode(),
            'modes' => Settings::MODES,
            'weights' => config('lunar.search_relevance.scoring.weights', []),
            'versions' => QueryReport::modelTypes()->map(fn (string $model) => [
                'model' => $model,
                'label' => class_basename($model),
                'version' => $version->current($model),
            ])->values()->all(),
            'urls' => ['update' => route('panel.settings.search-relevance.update')],
        ]);
    }

    public function update(Request $request, Settings $settings): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', Rule::in(Settings::MODES)],
        ]);

        $settings->setMode($validated['mode']);

        return back()->with('success', __('search-relevance::panel.settings_saved'));
    }
}
