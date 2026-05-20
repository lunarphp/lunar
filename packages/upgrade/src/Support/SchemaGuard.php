<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Support;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\QueryException;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;

class SchemaGuard
{
    public function __construct(protected ConnectionResolverInterface $connections) {}

    /**
     * Confirm the application has a migrations table on the configured connection.
     *
     * The detailed "all v1 migrations applied" check is contributed by future
     * specs that know the v1 migration names.
     */
    public function assertV1MigrationsApplied(?string $connection = null): void
    {
        try {
            $this->connections->connection($connection)
                ->table('migrations')
                ->count();
        } catch (QueryException) {
            throw new UpgradeAbortedException(
                'No migrations table found on the configured connection.',
                'Run `php artisan migrate` to bring v1.x up to date before upgrading.',
            );
        }
    }
}
