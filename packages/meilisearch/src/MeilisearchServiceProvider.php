<?php

namespace Lunar\Meilisearch;

use Illuminate\Support\ServiceProvider;
use Lunar\Meilisearch\Console\MeilisearchSetup;

class MeilisearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MeilisearchSetup::class,
            ]);
        }
    }
}
