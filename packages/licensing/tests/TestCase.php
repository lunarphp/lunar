<?php

namespace Lunar\Licensing\Tests;

use Lunar\Licensing\LicensingServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            LicensingServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        // $this->loadLaravelMigrations();
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
