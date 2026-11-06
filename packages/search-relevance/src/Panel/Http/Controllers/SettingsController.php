<?php

namespace Lunar\SearchRelevance\Panel\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Panel\Reports\QueryReport;
use Lunar\SearchRelevance\RetrievalVersion;

/**
 * Read-only status screen. Store behaviour is configured in code like the
 * rest of Lunar; this page answers "why does search behave like this"
 * without letting an admin change it.
 */
class SettingsController
{
    public function index(Repository $config, RetrievalVersion $version): Response
    {
        $lastRun = SearchQueryScore::query()->max('updated_at');

        return Inertia::render('search-relevance::Settings/Index', [
            'mode' => (string) $config->get('lunar.search_relevance.mode', 'shadow'),
            'mode_env' => 'LUNAR_SEARCH_RELEVANCE_MODE',
            'weights' => $config->get('lunar.search_relevance.scoring.weights', []),
            'schedule' => (string) $config->get('lunar.search_relevance.scoring.schedule', '02:00'),
            'last_run' => $lastRun ? (string) $lastRun : null,
            'versions' => QueryReport::modelTypes()->map(fn (string $model) => [
                'model' => $model,
                'label' => class_basename($model),
                'version' => $version->current($model),
            ])->values()->all(),
        ]);
    }
}
