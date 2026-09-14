<?php

namespace Lunar\Tests\SearchRelevance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Lunar\SearchRelevance\SearchRelevanceServiceProvider;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\SearchRelevance\Support\MigrationState;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        MigrationState::ensureFor(static::class);

        parent::setUp();

        activity()->disableLogging();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelDataServiceProvider::class,
            ScoutServiceProvider::class,
            SearchServiceProvider::class,
            SearchRelevanceServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('scout.driver', 'database');
        $app['config']->set('queue.default', 'sync');
    }
}
