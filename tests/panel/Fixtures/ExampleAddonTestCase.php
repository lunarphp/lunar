<?php

namespace Lunar\Tests\Panel\Fixtures;

use Lunar\Tests\Panel\TestCase;
use LunarPanelExample\ExampleAddonServiceProvider;

class ExampleAddonTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ExampleAddonServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // The real reference add-on ships its own page component
        // (Widgets/Index); register its path alongside the panel's own
        // and the test fixtures' for Inertia's testing finder.
        $app['config']->set('inertia.pages.paths', [
            ...$app['config']->get('inertia.pages.paths', []),
            dirname(__DIR__, 3).'/packages/panel-addon-example/resources/js/pages',
        ]);
    }
}
