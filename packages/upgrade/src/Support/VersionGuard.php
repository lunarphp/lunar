<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Support;

use Illuminate\Database\ConnectionResolverInterface;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;

class VersionGuard
{
    /**
     * Lunar v1.x migration filenames distinctive enough to identify a v1
     * install. A v1 schema will have most or all of these in the ledger;
     * the sniff treats *any* hit as a positive match so users who have
     * squashed older migrations still pass.
     */
    public const V1_CANARIES = [
        '2021_07_29_100000_create_channels_table',
        '2021_07_29_100004_create_attribute_groups_table',
        '2021_07_29_100020_create_products_table',
        '2021_07_29_100030_create_product_variants_table',
        '2021_07_29_100050_create_prices_table',
        '2021_10_01_090000_create_orders_table',
        '2021_10_01_100000_create_carts_table',
        '2022_11_18_100000_create_discounts_table',
    ];

    /**
     * v2 ships a flat baseline whose filenames all start with this prefix.
     * If the ledger already contains baseline rows the install is on v2 and
     * the upgrade command has nothing to do.
     */
    public const V2_BASELINE_PREFIX = '2026_01_01_';

    public function __construct(protected ConnectionResolverInterface $connections) {}

    public function assertV1SchemaPresent(?string $connection = null): void
    {
        $migrations = $this->connections->connection($connection)->table('migrations');

        $hasV2Baseline = (clone $migrations)
            ->where('migration', 'like', self::V2_BASELINE_PREFIX.'%')
            ->exists();

        if ($hasV2Baseline) {
            throw new UpgradeAbortedException(
                'The Lunar v2 baseline migrations are already recorded on this connection.',
                'Nothing to upgrade — clear the v2 baseline rows from the `migrations` table if you intended to re-run.',
            );
        }

        $hasV1Canary = (clone $migrations)
            ->whereIn('migration', self::V1_CANARIES)
            ->exists();

        if (! $hasV1Canary) {
            throw new UpgradeAbortedException(
                'No Lunar v1.x migration rows were found on the configured connection.',
                'The upgrade command only runs against a database that already has Lunar v1.x installed.',
            );
        }
    }
}
