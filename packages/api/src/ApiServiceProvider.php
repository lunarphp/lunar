<?php

namespace Lunar\Api;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Boot up the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register JSON:API Servers
        $this->app['config']->set('jsonapi.servers', [
            'storefront' => \Lunar\Api\Storefront\Server::class,
        ]);
    }
}
