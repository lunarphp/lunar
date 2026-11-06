<?php

namespace Lunar\SearchRelevance\Scoring;

use DateTimeInterface;
use Lunar\SearchRelevance\Models\SearchQuery;

/**
 * For every logged search that led to a purchase, compares where the bought
 * product sat in the shown order against the reranked order.
 */
class Replay
{
    public function run(DateTimeInterface $since, ?string $modelType = null): ReplayResult
    {
        $searches = 0;
        $shownSum = 0.0;
        $rankedSum = 0.0;
        $improved = 0;
        $worsened = 0;

        SearchQuery::query()
            ->where('created_at', '>=', $since)
            ->whereNotNull('ranked')
            ->when($modelType, fn ($query) => $query->where('model_type', $modelType))
            ->whereHas('events', fn ($query) => $query->where('type', 'purchase'))
            ->with(['events' => fn ($query) => $query->where('type', 'purchase')])
            ->orderBy('id')
            ->chunk(500, function ($chunk) use (&$searches, &$shownSum, &$rankedSum, &$improved, &$worsened) {
                foreach ($chunk as $search) {
                    $shown = array_map('intval', $search->shown ?? []);
                    $ranked = array_map('intval', $search->ranked ?? []);

                    foreach ($search->events->pluck('product_id')->unique() as $productId) {
                        $shownPosition = array_search((int) $productId, $shown, true);
                        $rankedPosition = array_search((int) $productId, $ranked, true);

                        if ($shownPosition === false && $rankedPosition === false) {
                            continue;
                        }

                        $shownRr = $shownPosition === false ? 0.0 : 1 / ($shownPosition + 1);
                        $rankedRr = $rankedPosition === false ? 0.0 : 1 / ($rankedPosition + 1);

                        $searches++;
                        $shownSum += $shownRr;
                        $rankedSum += $rankedRr;

                        if ($rankedRr > $shownRr) {
                            $improved++;
                        } elseif ($rankedRr < $shownRr) {
                            $worsened++;
                        }
                    }
                }
            });

        return new ReplayResult(
            searches: $searches,
            mrrShown: $searches ? $shownSum / $searches : 0.0,
            mrrRanked: $searches ? $rankedSum / $searches : 0.0,
            improvedShare: $searches ? $improved / $searches : 0.0,
            worsenedShare: $searches ? $worsened / $searches : 0.0,
        );
    }
}
