<?php

namespace Lunar\ScoutDatabaseEngine\Tests;

use Lunar\ScoutDatabaseEngine\ScoutDatabaseServiceProvider;
use Laravel\Scout\ScoutServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set(
            'scout',
            [
                'driver' => 'database_index',
                'prefix' => '',
                'queue' => false,
                'after_commit' => false,
                'chunk' => [
                    'searchable' => 500,
                    'unsearchable' => 500,
                ],
                'soft_delete' => false,
                'identify' => false,
            ]
        );
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, string>
     */
    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ScoutDatabaseServiceProvider::class,
            TestServiceProvider::class,
        ];
    }
}
