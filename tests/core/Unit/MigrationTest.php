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

    // Re-apply migrations so subsequent tests find the schema intact.
    // RefreshDatabase only runs `migrate:fresh` once per process when the
    // testing connection is file-backed; without this we leave the DB stripped.
    artisan('migrate');
});

test('each migration can run and rollback', function () {
    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ));

    foreach ($migrationsList as $migration) {
        artisan('migrate', [
            '--realpath' => $migration->getRealpath(),
        ]);

        assertDatabaseHas('migrations', [
            'migration' => pathinfo($migration->getFilename(), PATHINFO_FILENAME),
        ]);

        artisan('migrate:rollback', [
            '--realpath' => $migration->getRealpath(),
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => pathinfo($migration->getFilename(), PATHINFO_FILENAME),
        ]);

        artisan('migrate', [
            '--realpath' => $migration->getRealpath(),
        ]);
    }
})->group('slow');
