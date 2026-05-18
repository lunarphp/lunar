<?php

namespace Lunar\Tests\Search;

use Illuminate\Support\Facades\Config;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stubs\User;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

use function Orchestra\Testbench\after_resolving;
use function Orchestra\Testbench\default_migration_path;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('services.stripe.key', 'SK_TESTER');
        Config::set('services.stripe.webhooks.lunar', 'FOOBAR');

        activity()->disableLogging();

        Stripe::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelDataServiceProvider::class,
            SearchServiceProvider::class,
            ScoutServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
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
    protected function defineDatabaseMigrations()
    {
        after_resolving($this->app, 'migrator', static function ($migrator) {
            $migrator->path(default_migration_path());
        });
    }
}
