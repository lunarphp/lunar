<?php

namespace Lunar\SearchRelevance\Scoring;

use DateTimeInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;

/**
 * One aggregation query per run. Drivers differ only in how they express the
 * event age in seconds, the minute bucket for the search-rate guard, and
 * whether a bound float needs a cast.
 *
 * Abuse guards applied here: each session contributes at most one event of
 * each type per query and product; only trusted sessions (a cart or a known
 * customer) count when `trusted_sessions_only` is set; excluded products
 * and events before a query reset are ignored.
 */
abstract class SqlScoreAggregator implements ScoreAggregator
{
    public function __construct(
        protected ConnectionResolverInterface $db,
        protected Repository $config,
        protected QueryAffinitySignal $affinity,
    ) {}

    /** Age of `e.created_at` in seconds relative to a bound job-start timestamp (`?`), not the database clock, so time zones cannot skew the decay. */
    abstract protected function ageSecondsExpression(): string;

    abstract protected function minuteBucketExpression(): string;

    /** A `?` placeholder for a float binding, with a cast where the driver needs one. */
    abstract protected function floatPlaceholder(): string;

    public function aggregate(string $version, array $config): int
    {
        $start = now();
        $connection = $this->connection();
        $prefix = $this->config->get('lunar.database.table_prefix');
        $events = $prefix.'search_events';
        $queries = $prefix.'search_queries';
        $scores = $prefix.'search_query_scores';
        $overrides = $prefix.'search_learning_overrides';
        $trustedOnly = (bool) ($config['trusted_sessions_only'] ?? false);
        $trust = $trustedOnly ? "AND (q.session_id LIKE 'cart:%' OR q.customer_id IS NOT NULL)" : '';

        $weights = $config['weights'] ?? [];
        $windowStart = $start->copy()->subDays((int) ($config['window_days'] ?? 180));
        $f = $this->floatPlaceholder();
        $age = $this->ageSecondsExpression();
        $minute = $this->minuteBucketExpression();

        $sql = <<<SQL
            WITH busy AS (
                SELECT session_id
                FROM {$queries}
                WHERE created_at > ?
                GROUP BY session_id, {$minute}
                HAVING COUNT(*) > ?
            ),
            resets AS (
                SELECT model_type, normalised_query, MAX(created_at) AS reset_at
                FROM {$overrides}
                WHERE type = 'reset'
                GROUP BY model_type, normalised_query
            ),
            deduped AS (
                SELECT q.model_type, q.normalised_query, e.product_id, e.session_id, e.type,
                    MIN(e.source) AS source, MIN(e.position) AS position, MAX(e.created_at) AS created_at
                FROM {$events} e
                JOIN {$queries} q ON q.id = e.search_id
                LEFT JOIN resets r ON r.model_type = q.model_type AND r.normalised_query = q.normalised_query
                WHERE e.created_at > ?
                  AND (r.reset_at IS NULL OR e.created_at > r.reset_at)
                  AND q.version = ?
                  AND e.session_id NOT IN (SELECT session_id FROM busy)
                  {$trust}
                  AND NOT EXISTS (
                      SELECT 1 FROM {$overrides} x
                      WHERE x.type = 'exclude' AND x.model_type = q.model_type
                        AND x.normalised_query = q.normalised_query AND x.product_id = e.product_id
                  )
                GROUP BY q.model_type, q.normalised_query, e.product_id, e.session_id, e.type
            ),
            raw AS (
                SELECT e.model_type, e.normalised_query, e.product_id,
                    SUM(
                        (CASE e.type WHEN 'click' THEN {$f} WHEN 'basket' THEN {$f} WHEN 'purchase' THEN {$f} ELSE 0 END)
                        * (CASE WHEN e.source = 'explore' THEN 1 ELSE LEAST(POWER(e.position, {$f}), {$f}) END)
                        * POWER(0.5, ({$age}) / 86400.0 / {$f})
                    ) AS score,
                    COUNT(DISTINCT e.session_id) AS sessions
                FROM deduped e
                GROUP BY e.model_type, e.normalised_query, e.product_id
                HAVING COUNT(DISTINCT e.session_id) >= ?
            ),
            ranked AS (
                SELECT model_type, normalised_query, product_id, score, sessions,
                    score / MAX(score) OVER (PARTITION BY model_type, normalised_query) AS relative,
                    ROW_NUMBER() OVER (PARTITION BY model_type, normalised_query ORDER BY score DESC, product_id ASC) AS rn
                FROM raw
            )
            SELECT model_type, normalised_query, product_id, score, relative, sessions
            FROM ranked
            WHERE rn <= ?
            SQL;

        $rows = $connection->select($sql, [
            $windowStart,
            (int) ($config['max_searches_per_minute'] ?? PHP_INT_MAX),
            $windowStart,
            $version,
            (float) ($weights['click'] ?? 1),
            (float) ($weights['basket'] ?? 3),
            (float) ($weights['purchase'] ?? 5),
            (float) ($config['position_eta'] ?? 0.7),
            (float) ($config['max_position_weight'] ?? 5),
            $start->format('Y-m-d H:i:s'),
            (float) ($config['half_life_days'] ?? 30),
            (int) ($config['min_sessions'] ?? 3),
            (int) ($config['max_products_per_query'] ?? 50),
        ]);

        $written = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            $values = array_map(fn ($row) => [
                'model_type' => $row->model_type,
                'normalised_query' => $row->normalised_query,
                'product_id' => (int) $row->product_id,
                'score' => (float) $row->score,
                'relative' => (float) $row->relative,
                'sessions' => (int) $row->sessions,
                'version' => $version,
                'updated_at' => $start,
            ], $chunk);

            $connection->table($scores)->upsert(
                $values,
                ['model_type', 'normalised_query', 'product_id'],
                ['score', 'relative', 'sessions', 'version', 'updated_at'],
            );

            $written += count($values);
        }

        $this->sweep($connection->table($scores), $version, $config, $start);
        $this->affinity->forget();

        return $written;
    }

    /** Drop rows this run did not refresh, and rows of any version no longer in use. */
    protected function sweep(Builder $scores, string $version, array $config, DateTimeInterface $start): void
    {
        $keep = array_values(array_unique([...($config['keep_versions'] ?? []), $version]));

        (clone $scores)->where('version', $version)->where('updated_at', '<', $start)->delete();
        (clone $scores)->whereNotIn('version', $keep)->delete();
    }

    protected function connection(): Connection
    {
        return $this->db->connection($this->config->get('lunar.database.connection'));
    }
}
