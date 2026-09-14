<?php

namespace Lunar\SearchRelevance;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

/**
 * The effective ranking mode: the panel's persisted override when present,
 * else config. Bound scoped, so the memo lives for one request only.
 */
class Settings
{
    public const MODES = ['off', 'shadow', 'on'];

    protected ?string $mode = null;

    public function __construct(
        protected ConnectionResolverInterface $db,
        protected Repository $config,
    ) {}

    public function mode(): string
    {
        return $this->mode ??= $this->resolveMode();
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, self::MODES, true)) {
            throw new InvalidArgumentException("Unknown search relevance mode [{$mode}].");
        }

        $this->table()->upsert([[
            'key' => 'mode',
            'value' => json_encode($mode),
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['key'], ['value', 'updated_at']);

        $this->mode = null;
    }

    protected function resolveMode(): string
    {
        $fallback = (string) $this->config->get('lunar.search_relevance.mode', 'shadow');

        try {
            $row = $this->table()->where('key', 'mode')->value('value');
        } catch (QueryException) {
            // The table is created by this package's migration; before it runs config rules.
            return $fallback;
        }

        $mode = $row === null ? null : json_decode($row, true);

        return is_string($mode) && in_array($mode, self::MODES, true) ? $mode : $fallback;
    }

    protected function table(): Builder
    {
        return $this->db
            ->connection($this->config->get('lunar.database.connection'))
            ->table($this->config->get('lunar.database.table_prefix').'search_relevance_settings');
    }
}
