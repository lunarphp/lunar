<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

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
});
