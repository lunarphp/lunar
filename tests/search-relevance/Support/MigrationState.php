<?php

namespace Lunar\Tests\SearchRelevance\Support;

use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * RefreshDatabase migrates once per process, with whichever providers the
 * first test case booted. This suite mixes two test cases with different
 * provider sets (and the cross-db group mixes in core's), so force a fresh
 * migration whenever the test case class changes within a worker.
 */
final class MigrationState
{
    private static ?string $testCase = null;

    public static function ensureFor(string $testCase): void
    {
        // A different class already migrated this process: ours (first call
        // here) or another suite's in the cross-db group (never calls here).
        if (self::$testCase !== $testCase && RefreshDatabaseState::$migrated) {
            RefreshDatabaseState::$migrated = false;
        }

        self::$testCase = $testCase;
    }
}
