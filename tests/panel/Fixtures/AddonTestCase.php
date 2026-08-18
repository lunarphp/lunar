<?php

namespace Lunar\Tests\Panel\Fixtures;

use Lunar\Tests\Panel\Fixtures\Addon\AddonServiceProvider;
use Lunar\Tests\Panel\TestCase;

class AddonTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            AddonServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // The fixture add-on ships its own page component (Widgets/Index);
        // register its path alongside the panel's own for Inertia's testing finder.
        $app['config']->set('inertia.pages.paths', [
            ...$app['config']->get('inertia.pages.paths', []),
            __DIR__.'/resources/js/pages',
        ]);
    }
}
