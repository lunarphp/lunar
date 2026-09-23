<?php

namespace Lunar\SearchRelevance\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

class PruneCommand extends Command
{
    protected $signature = 'lunar:search-relevance:prune';

    protected $description = 'Delete logged searches and events older than the retention period';

    public function handle(Repository $config): int
    {
        $cutoff = now()->subDays((int) $config->get('lunar.search_relevance.retention_days', 400));

        $events = SearchEvent::query()->where('created_at', '<', $cutoff)->delete();
        $queries = SearchQuery::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$queries} searches and {$events} events older than {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
