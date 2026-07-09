<?php

namespace Lunar\Tests\Panel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Panel\PanelServiceProvider;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        activity()->disableLogging();

        // The root Blade view calls the Vite facade; no manifest exists
        // under Testbench, so swap in the no-op fake.
        $this->withoutVite();

        $this->freezeTime();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            InertiaServiceProvider::class,
            PanelServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users.model', User::class);

        // Inertia's testing component-exists check looks under the Testbench
        // skeleton's resource_path() by default; point it at the panel
        // package's own page components instead.
        $app['config']->set('inertia.testing.page_paths', [
            dirname(__DIR__, 2).'/packages/panel/resources/js/pages',
        ]);
    }
}
