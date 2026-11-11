<?php

namespace Lunar\Tests\Shipping;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Lunar\Panel\PanelServiceProvider;

/**
 * Boots the Inertia panel alongside the shipping package, so the Settings >
 * Shipping section can be exercised against the real panel harness.
 */
class PanelTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The panel's root Blade view calls the Vite facade; no manifest
        // exists under Testbench, so swap in the no-op fake.
        $this->withoutVite();
    }

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            InertiaServiceProvider::class,
            PanelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // The section's pages resolve client-side through
        // window.LunarPanel.registerPages(); register their on-disk path so
        // Inertia's testing finder can check them too.
        $app['config']->set('inertia.pages.paths', [
            dirname(__DIR__, 2).'/packages/panel/resources/js/pages',
            dirname(__DIR__, 2).'/packages/table-rate-shipping/resources/js/pages',
        ]);
    }
}
