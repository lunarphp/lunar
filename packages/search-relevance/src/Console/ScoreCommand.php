<?php

namespace Lunar\SearchRelevance\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\RetrievalVersion;

class ScoreCommand extends Command
{
    protected $signature = 'lunar:search-relevance:score';

    protected $description = 'Aggregate search events into per-query product scores';

    public function handle(ScoreAggregator $aggregator, RetrievalVersion $version, Repository $config): int
    {
        $models = $config->get('lunar.search_relevance.models', []);
        $versions = collect($models)->map(fn (string $model) => $version->current($model))->unique()->values();

        $scoring = [
            ...$config->get('lunar.search_relevance.scoring', []),
            'max_searches_per_minute' => $config->get('lunar.search_relevance.guards.max_searches_per_minute', 30),
            'trusted_sessions_only' => (bool) $config->get('lunar.search_relevance.guards.trusted_sessions_only', true),
            'keep_versions' => $versions->all(),
        ];

        foreach ($versions as $current) {
            $written = $aggregator->aggregate($current, $scoring);
            $this->info("[{$current}] wrote {$written} query/product rows.");
        }

        return self::SUCCESS;
    }
}
