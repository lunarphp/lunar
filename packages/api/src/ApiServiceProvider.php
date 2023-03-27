<?php

namespace Lunar\Api;

use Illuminate\Support\ServiceProvider;
use LaravelJsonApi\HashIds\HashId;

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
            'checkout' => \Lunar\Api\Checkout\Server::class,
            'admin' => \Lunar\Api\Admin\Server::class,
        ]);

        // Set Hash IDs connection
        $this->app['config']->set('hashids.connections.lunar', [
            'salt' => '',
            'length' => 16,
        ]);

        HashId::withDefaultConnection('lunar');
    }
}
