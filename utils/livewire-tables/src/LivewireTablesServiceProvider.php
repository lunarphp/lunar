<?php

namespace Lunar\LivewireTables;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\LivewireTables\Components\Actions\BulkAction;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Filters\SelectFilter;
use Lunar\LivewireTables\Components\Head;
use Lunar\LivewireTables\Components\Table;
use Lunar\LivewireTables\Support\TableBuilder;
use Lunar\LivewireTables\Support\TableBuilderInterface;

class LivewireTablesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TableBuilderInterface::class, function ($app) {
           return $app->make(TableBuilder::class);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/livewire-tables.php', 'livewire-tables');
    }

    public function boot()
    {
        $components = [
            Table::class,
            TextColumn::class,
            Head::class,
            SelectFilter::class,
            BulkAction::class,
        ];

        foreach ($components as $component) {
            Livewire::component((new $component)->getName(), $component);
        }

        Blade::componentNamespace('Lunar\\LivewireTables\\View', 'l-tables');

        Blade::directive('livewireTableStyles', function () {
            $manifest = json_decode(file_get_contents(__DIR__.'/../dist/mix-manifest.json'), true);

            $cssUrl = asset('/vendor/lunar'.$manifest['/livewire-tables/app.css']);

            return <<<EOT
                <link rel="stylesheet" href="{$cssUrl}" />
            EOT;
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'l-tables');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar'),
        ], 'lunar.livewiretables.components');

        $this->publishes([
            __DIR__.'/../dist' => public_path('vendor/lunar'),
        ], 'lunar.livewiretables.public');
    }
}
