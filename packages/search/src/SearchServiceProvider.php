<?php

namespace Lunar\Search;

use Illuminate\Support\ServiceProvider;
use Lunar\Search\Contracts\SearchManagerContract;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SearchManagerContract::class, fn ($app) => $app->make(SearchManager::class));
    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/search.php', 'lunar.search');
    }
}
