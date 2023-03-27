<?php

namespace Lunar\Api\Storefront;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/storefront';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        // no-op
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            Brands\BrandSchema::class,
            CollectionGroups\CollectionGroupSchema::class,
            Collections\CollectionSchema::class,
            Products\ProductSchema::class,
            ProductTypes\ProductTypeSchema::class,
        ];
    }
}
