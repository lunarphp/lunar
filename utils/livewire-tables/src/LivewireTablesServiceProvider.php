<?php

namespace GetCandy\LivewireTables;

use GetCandy\LivewireTables\Components\Actions\BulkAction;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Filters\SelectFilter;
use GetCandy\LivewireTables\Components\Head;
use GetCandy\LivewireTables\Components\Table;
use GetCandy\LivewireTables\Support\TableBuilder;
use GetCandy\LivewireTables\Support\TableBuilderInterface;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        Blade::componentNamespace('GetCandy\\LivewireTables\\View', 'tables');

        Blade::directive('livewireTableStyles', function () {
            $manifest = json_decode(file_get_contents(__DIR__.'/../dist/mix-manifest.json'), true);

            $cssUrl = asset('/vendor/getcandy'.$manifest['/livewire-tables/app.css']);

            return <<<EOT
                <link rel="stylesheet" href="{$cssUrl}" />
            EOT;
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tables');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/getcandy'),
        ], 'getcandy.livewiretables.components');

        $this->publishes([
            __DIR__.'/../dist' => public_path('vendor/getcandy'),
        ], 'getcandy.livewiretables.public');
    }
}
