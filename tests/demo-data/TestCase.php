<?php

namespace Lunar\Tests\DemoData;

use Illuminate\Support\Facades\Config;
use Lunar\Core\LunarServiceProvider;
use Lunar\DemoData\DemoDataServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('providers.users.model', User::class);
        Config::set('auth.providers.users.model', User::class);
        Config::set('lunar.urls.generator', null);
        activity()->disableLogging();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            DemoDataServiceProvider::class,
            BlinkServiceProvider::class,
        ];
    }
}
