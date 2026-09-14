<?php

namespace Lunar\SearchRelevance\Scoring;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Carbon;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;

/**
 * The same aggregation as the SQL aggregators, computed in chunked PHP for
 * drivers without window functions in a CTE we can rely on (SQLite).
 */
class PhpScoreAggregator implements ScoreAggregator
{
    public function __construct(
        protected ConnectionResolverInterface $db,
        protected Repository $config,
        protected QueryAffinitySignal $affinity,
    ) {}

    public function aggregate(string $version, array $config): int
    {
        $start = now();
        $connection = $this->connection();
        $prefix = $this->config->get('lunar.database.table_prefix');

        $weights = $config['weights'] ?? [];
        $eta = (float) ($config['position_eta'] ?? 0.7);
        $maxPositionWeight = (float) ($config['max_position_weight'] ?? 5);
        $halfLife = (float) ($config['half_life_days'] ?? 30);
        $minSessions = (int) ($config['min_sessions'] ?? 3);
        $maxProducts = (int) ($config['max_products_per_query'] ?? 50);
        $windowStart = $start->copy()->subDays((int) ($config['window_days'] ?? 180));
        $busy = $this->busySessions($connection, $prefix, $windowStart, (int) ($config['max_searches_per_minute'] ?? PHP_INT_MAX));
        $trustedOnly = (bool) ($config['trusted_sessions_only'] ?? false);
        [$excluded, $resets] = $this->overrides($connection, $prefix);

        /** @var array<string, array{model_type: string, query: string, product_id: int, score: float, sessions: array<string, true>}> $groups */
        $groups = [];
        /** @var array<string, true> $seen one event per query, product, session and type */
        $seen = [];

        $connection->table($prefix.'search_events as e')
            ->join($prefix.'search_queries as q', 'q.id', '=', 'e.search_id')
            ->where('e.created_at', '>', $windowStart)
            ->where('q.version', $version)
            ->select(['e.id', 'q.model_type', 'q.normalised_query', 'q.customer_id', 'e.product_id', 'e.type', 'e.source', 'e.position', 'e.session_id', 'e.created_at'])
            ->orderBy('e.id')
            ->chunk(1000, function ($events) use (&$groups, &$seen, $busy, $trustedOnly, $excluded, $resets, $weights, $eta, $maxPositionWeight, $halfLife, $start) {
                foreach ($events as $event) {
                    if (isset($busy[$event->session_id])) {
                        continue;
                    }

                    if ($trustedOnly && ! str_starts_with($event->session_id, 'cart:') && $event->customer_id === null) {
                        continue;
                    }

                    $queryKey = $event->model_type.'|'.$event->normalised_query;

                    if (isset($excluded[$queryKey][(int) $event->product_id])) {
                        continue;
                    }

                    if (isset($resets[$queryKey]) && Carbon::parse($event->created_at)->lte($resets[$queryKey])) {
                        continue;
                    }

                    $seenKey = $queryKey.'|'.$event->product_id.'|'.$event->session_id.'|'.$event->type;

                    if (isset($seen[$seenKey])) {
                        continue;
                    }
                    $seen[$seenKey] = true;

                    $weight = (float) ($weights[$event->type] ?? 0);
                    $positionWeight = $event->source === 'explore' ? 1.0 : min(pow((int) $event->position, $eta), $maxPositionWeight);
                    $ageDays = max(0, $start->getTimestamp() - Carbon::parse($event->created_at)->getTimestamp()) / 86400;
                    $decay = pow(0.5, $ageDays / $halfLife);

                    $key = $event->model_type.'|'.$event->normalised_query.'|'.$event->product_id;
                    $groups[$key] ??= [
                        'model_type' => $event->model_type,
                        'query' => $event->normalised_query,
                        'product_id' => (int) $event->product_id,
                        'score' => 0.0,
                        'sessions' => [],
                    ];
                    $groups[$key]['score'] += $weight * $positionWeight * $decay;
                    $groups[$key]['sessions'][$event->session_id] = true;
                }
            });

        $byQuery = [];

        foreach ($groups as $group) {
            if (count($group['sessions']) < $minSessions) {
                continue;
            }

            $byQuery[$group['model_type'].'|'.$group['query']][] = $group;
        }

        $rows = [];

        foreach ($byQuery as $products) {
            usort($products, fn ($a, $b) => [$b['score'], $a['product_id']] <=> [$a['score'], $b['product_id']]);
            $max = $products[0]['score'] ?: 1.0;

            foreach (array_slice($products, 0, $maxProducts) as $product) {
                $rows[] = [
                    'model_type' => $product['model_type'],
                    'normalised_query' => $product['query'],
                    'product_id' => $product['product_id'],
                    'score' => $product['score'],
                    'relative' => $product['score'] / $max,
                    'sessions' => count($product['sessions']),
                    'version' => $version,
                    'updated_at' => $start,
                ];
            }
        }

        $scores = $connection->table($prefix.'search_query_scores');

        foreach (array_chunk($rows, 500) as $chunk) {
            $scores->upsert($chunk, ['model_type', 'normalised_query', 'product_id'], ['score', 'relative', 'sessions', 'version', 'updated_at']);
        }

        $keep = array_values(array_unique([...($config['keep_versions'] ?? []), $version]));
        (clone $scores)->where('version', $version)->where('updated_at', '<', $start)->delete();
        (clone $scores)->whereNotIn('version', $keep)->delete();

        $this->affinity->forget();

        return count($rows);
    }

    /**
     * Sessions that searched more than the guard allows in any single minute.
     *
     * @return array<string, true>
     */
    protected function busySessions(Connection $connection, string $prefix, Carbon $windowStart, int $max): array
    {
        $counts = [];

        $connection->table($prefix.'search_queries')
            ->where('created_at', '>', $windowStart)
            ->select(['session_id', 'created_at'])
            ->orderBy('id')
            ->chunk(1000, function ($searches) use (&$counts) {
                foreach ($searches as $search) {
                    $minute = Carbon::parse($search->created_at)->format('Y-m-d H:i');
                    $counts[$search->session_id][$minute] = ($counts[$search->session_id][$minute] ?? 0) + 1;
                }
            });

        $busy = [];

        foreach ($counts as $sessionId => $minutes) {
            if (max($minutes) > $max) {
                $busy[$sessionId] = true;
            }
        }

        return $busy;
    }

    /**
     * Excluded products per query, and the latest reset per query.
     *
     * @return array{0: array<string, array<int, true>>, 1: array<string, Carbon>}
     */
    protected function overrides(Connection $connection, string $prefix): array
    {
        $excluded = [];
        $resets = [];

        foreach ($connection->table($prefix.'search_learning_overrides')->get() as $override) {
            $key = $override->model_type.'|'.$override->normalised_query;

            if ($override->type === 'exclude') {
                $excluded[$key][(int) $override->product_id] = true;
            } elseif ($override->type === 'reset') {
                $at = Carbon::parse($override->created_at);
                $resets[$key] = isset($resets[$key]) && $resets[$key]->gt($at) ? $resets[$key] : $at;
            }
        }

        return [$excluded, $resets];
    }

    protected function connection(): Connection
    {
        return $this->db->connection($this->config->get('lunar.database.connection'));
    }
}
