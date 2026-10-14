<?php

namespace Lunar\SearchRelevance\Console;

use Illuminate\Console\Command;
use Lunar\SearchRelevance\Scoring\Replay;

class ReplayCommand extends Command
{
    protected $signature = 'lunar:search-relevance:replay {--days=30 : How far back to replay}';

    protected $description = 'Compare the shown and reranked position of purchased products in logged searches';

    public function handle(Replay $replay): int
    {
        $days = max(1, (int) $this->option('days'));
        $result = $replay->run(now()->subDays($days));

        $this->table(['Metric', 'Value'], [
            ['Searches with a purchase', $result->searches],
            ['MRR (shown order)', number_format($result->mrrShown, 3)],
            ['MRR (reranked order)', number_format($result->mrrRanked, 3)],
            ['Improved', number_format($result->improvedShare * 100, 1).'%'],
            ['Worsened', number_format($result->worsenedShare * 100, 1).'%'],
        ]);

        return self::SUCCESS;
    }
}
