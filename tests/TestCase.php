<?php

namespace Lunar\Tests;

use Lunar\Facades\ModelManifest;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\StructureDiscoverer\Discover;

use function Orchestra\Testbench\after_resolving;
use function Orchestra\Testbench\default_migration_path;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->replaceModelsForTesting();

        // File-backed SQLite per worker; Testbench wipes RefreshDatabase's
        // cache for `:memory:`, forcing a full migrate every test.
        $dbPath = sys_get_temp_dir().'/lunar-test-'.getmypid().'.sqlite';
        if (! file_exists($dbPath)) {
            touch($dbPath);
        }

        $app['config']->set('database.connections.testing.database', $dbPath);
    }

    // Register laravel migrations on the migrator instead of running them
    // separately — a standalone migrate commits DDL and resets RefreshDatabase's
    // per-process cache.
    protected function defineDatabaseMigrations(): void
    {
        after_resolving($this->app, 'migrator', static function ($migrator) {
            $migrator->path(default_migration_path());
        });
    }

    /**
     * Replace Lunar models with test models for testing
     * functionality with model extending.
     */
    protected function replaceModelsForTesting(): void
    {
        if (! env('LUNAR_TESTING_REPLACE_MODELS', false)) {
            return;
        }

        $modelClasses = Discover::in(__DIR__.'/core/Stubs/Models')
            ->classes()
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = ModelManifest::guessContractClass($modelClass);
            ModelManifest::replace($interfaceClass, $modelClass);
        }
    }
}
