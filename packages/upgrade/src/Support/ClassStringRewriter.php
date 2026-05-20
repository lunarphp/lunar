<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class ClassStringRewriter
{
    /**
     * Rewrite class strings stored in a single column.
     *
     * @param  array<string, string>  $map  from-class => to-class
     * @return int rows affected
     */
    public function rewrite(string $table, string $column, array $map, ?string $connection = null): int
    {
        $connection = $this->connection($connection);

        $affected = 0;

        foreach ($map as $from => $to) {
            $affected += $connection->table($table)
                ->where($column, $from)
                ->update([$column => $to]);
        }

        return $affected;
    }

    /**
     * Count rows that would be rewritten without modifying them.
     *
     * @param  array<string, string>  $map
     * @return array<string, int> from-class => row count
     */
    public function dryRun(string $table, string $column, array $map, ?string $connection = null): array
    {
        $connection = $this->connection($connection);

        $counts = [];

        foreach ($map as $from => $to) {
            $counts[$from] = $connection->table($table)
                ->where($column, $from)
                ->count();
        }

        return $counts;
    }

    protected function connection(?string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
