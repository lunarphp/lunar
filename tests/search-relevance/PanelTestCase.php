<?php

namespace Lunar\Tests\SearchRelevance;

use Laravel\Scout\ScoutServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Lunar\SearchRelevance\SearchRelevanceServiceProvider;
use Lunar\Tests\Panel\TestCase as PanelBaseTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

/** Boots the panel with the search-relevance add-on registered, for its panel section tests. */
class PanelTestCase extends PanelBaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ScoutServiceProvider::class,
            LaravelDataServiceProvider::class,
            SearchServiceProvider::class,
            SearchRelevanceServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('scout.driver', 'database');
        $app['config']->set('queue.default', 'sync');

        $app['config']->set('inertia.pages.paths', [
            ...$app['config']->get('inertia.pages.paths', []),
            dirname(__DIR__, 2).'/packages/search-relevance/resources/js/pages',
        ]);
    }
}
