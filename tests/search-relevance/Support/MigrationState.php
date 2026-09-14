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
        if (self::$testCase !== null && self::$testCase !== $testCase) {
            RefreshDatabaseState::$migrated = false;
        }

        self::$testCase = $testCase;
    }
}
