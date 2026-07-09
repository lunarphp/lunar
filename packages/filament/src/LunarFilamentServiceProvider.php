<?php

namespace Lunar\Filament;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Filament\Models\Staff;
use Lunar\Filament\Support\ComponentExtensions\Registry as ComponentExtensionsRegistry;
use Lunar\Filament\Support\Facades\AttributeData as AttributeDataFacade;
use Lunar\Filament\Support\Forms\AttributeData;
use Lunar\Filament\Synthesizers\PriceSynth;

class LunarFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament.php',
            'lunar.filament'
        );

        $this->app['config']->set('lunar.staff.model', Staff::class);

        $this->app->singleton(ComponentExtensionsRegistry::class, function () {
            return new ComponentExtensionsRegistry;
        });

        $this->app->alias(ComponentExtensionsRegistry::class, 'lunar-filament.extensions');

        $this->app->scoped('lunar-attribute-data', function (): AttributeData {
            return new AttributeData;
        });

        $this->app->alias('lunar-attribute-data', AttributeData::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__.'/../resources/lang',
            'lunar-filament'
        );

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'lunar-filament'
        );

        $this->publishes([
            __DIR__.'/../config/filament.php' => config_path('lunar/filament.php'),
        ], 'lunar-filament.config');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/lunar-filament'),
        ], 'lunar-filament.lang');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar-filament'),
        ], 'lunar-filament.views');

        $this->publishes([
            __DIR__.'/Schemas' => config('lunar.filament.publish_path', app_path('Filament')).'/Schemas',
            __DIR__.'/Tables' => config('lunar.filament.publish_path', app_path('Filament')).'/Tables',
            __DIR__.'/RelationManagers' => config('lunar.filament.publish_path', app_path('Filament')).'/RelationManagers',
        ], 'lunar-filament.schemas');

        $this->registerSynthesizers();
    }

    protected function registerSynthesizers(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        AttributeDataFacade::synthesizeLivewireProperties();

        Livewire::propertySynthesizer(PriceSynth::class);
    }
}
