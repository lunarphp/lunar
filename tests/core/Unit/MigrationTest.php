<?php

use Illuminate\Support\Facades\File;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(TestCase::class)->group('migrations');

test('all migrations can run rollback', function () {
    artisan('migrate');

    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ))->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME));

    foreach ($migrationsList as $migration) {
        assertDatabaseHas('migrations', [
            'migration' => $migration,
        ]);
    }

    artisan('migrate:rollback');

    // Restore the schema for subsequent tests — RefreshDatabase only migrates
    // once per process on a file-backed connection.
    artisan('migrate');
});

test('migrations roll back and re-apply', function () {
    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ))->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME));

    artisan('migrate');
    artisan('migrate:rollback');

    foreach ($migrationsList as $migration) {
        assertDatabaseMissing('migrations', ['migration' => $migration]);
    }

    artisan('migrate');

    foreach ($migrationsList as $migration) {
        assertDatabaseHas('migrations', ['migration' => $migration]);
    }
});

// The v1 `add_unique_lunar_product_product_option` migration moved to the
// upgrade package; the v2 baseline ships the unique constraint in the
// `create_product_product_option_table` migration directly. The dedupe
// behaviour is exercised by the upgrade package's tests under spec 0001.
