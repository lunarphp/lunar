<?php

namespace GetCandy\Hub\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use GetCandy\GetCandyServiceProvider;
use GetCandy\Hub\AdminHubServiceProvider;
use GetCandy\Tests\Stubs\TestUrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('auth.guards.staff', [
            'driver' => 'getcandyhub',
        ]);
        Config::set('getcandy.urls.generator', TestUrlGenerator::class);

        View::addLocation(__DIR__.'/resources/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            GetCandyServiceProvider::class,
            LivewireServiceProvider::class,
            AdminHubServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            NotificationsServiceProvider::class,
            TablesServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
