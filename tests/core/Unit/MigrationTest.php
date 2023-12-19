<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Facades\File;

test('all migrations can run rollback', function () {
    $this->artisan('migrate');

    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ))->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME));

    foreach ($migrationsList as $migration) {
        $this->assertDatabaseHas('migrations', [
            'migration' => $migration,
        ]);
    }

    $this->artisan('migrate:rollback');
});

test('each migration can run and rollback', function () {
    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ));

    foreach ($migrationsList as $migration) {
        $this->artisan('migrate', [
            '--realpath' => $migration->getRealpath(),
        ]);

        $this->assertDatabaseHas('migrations', [
            'migration' => pathinfo($migration->getFilename(), PATHINFO_FILENAME),
        ]);

        $this->artisan('migrate:rollback', [
            '--realpath' => $migration->getRealpath(),
        ]);

        $this->assertDatabaseMissing('migrations', [
            'migration' => pathinfo($migration->getFilename(), PATHINFO_FILENAME),
        ]);

        $this->artisan('migrate', [
            '--realpath' => $migration->getRealpath(),
        ]);
    }
});
