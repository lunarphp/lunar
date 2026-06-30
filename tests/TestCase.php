<?php

namespace Lunar\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as BaseTestCase;

use function Orchestra\Testbench\after_resolving;
use function Orchestra\Testbench\default_migration_path;

class TestCase extends BaseTestCase
{
    private static bool $databasePrepared = false;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        Model::preventLazyLoading();

        match (env('DB_DRIVER', 'sqlite')) {
            'mysql' => $this->configureMysql($app),
            'pgsql' => $this->configurePgsql($app),
            default => $this->configureSqlite($app),
        };
    }

    private function configureSqlite($app): void
    {
        // File-backed SQLite per worker; Testbench wipes RefreshDatabase's
        // cache for `:memory:`, forcing a full migrate every test.
        $dbPath = sys_get_temp_dir().'/lunar-test-'.$this->workerToken().'.sqlite';
        if (! file_exists($dbPath)) {
            touch($dbPath);
        }

        $app['config']->set('database.connections.testing.database', $dbPath);
    }

    private function configureMysql($app): void
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $database = env('DB_DATABASE', 'lunar_test').'_'.$this->workerToken();

        if (! static::$databasePrepared) {
            $pdo = new \PDO("mysql:host={$host};port={$port}", $username, $password);
            $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            static::$databasePrepared = true;
        }

        $app['config']->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);
    }

    private function configurePgsql($app): void
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '5432');
        $username = env('DB_USERNAME', 'postgres');
        $password = env('DB_PASSWORD', '');
        $database = env('DB_DATABASE', 'lunar_test').'_'.$this->workerToken();

        if (! static::$databasePrepared) {
            $pdo = new \PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password);
            $pdo->exec("DROP DATABASE IF EXISTS \"{$database}\"");
            $pdo->exec("CREATE DATABASE \"{$database}\"");
            static::$databasePrepared = true;
        }

        $app['config']->set('database.connections.testing', [
            'driver' => 'pgsql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);
    }

    private function workerToken(): string
    {
        return (string) (env('TEST_TOKEN') ?: getmypid());
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
}
