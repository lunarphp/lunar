<?php

namespace Lunar\Tests\Bundles\Support;

use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * RefreshDatabase migrates once per process, with whichever providers the
 * first test case booted. This suite mixes two test cases with different
 * provider sets (headless and panel), so force a fresh migration whenever
 * the test case class changes within a worker.
 */
final class MigrationState
{
    private static ?string $testCase = null;

    public static function ensureFor(string $testCase): void
    {
        if (self::$testCase !== $testCase && RefreshDatabaseState::$migrated) {
            RefreshDatabaseState::$migrated = false;
        }

        self::$testCase = $testCase;
    }
}
