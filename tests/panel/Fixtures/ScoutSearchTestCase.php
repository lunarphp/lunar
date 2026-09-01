<?php

namespace Lunar\Tests\Panel\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\Tests\Panel\TestCase;

/**
 * Exercises the global search's Scout path. Scout's `collection` driver
 * searches the database directly, so the opt-in wiring is verified without a
 * live engine — typo tolerance itself comes from Meilisearch or Typesense.
 */
class ScoutSearchTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Scout's collection driver builds each candidate's searchable array
        // at query time, and the indexers read relations to do it; a real
        // engine does that work at index time instead.
        Model::preventLazyLoading(false);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ScoutServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('scout.driver', 'collection');
        $app['config']->set('lunar.panel.search.scout_enabled', true);
    }
}
