<?php

namespace Lunar\Api\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IsApiResource;
use Lunar\Api\Resources;
use Lunar\Api\State\ModelManifestCollectionProvider;
use Lunar\Api\State\ModelManifestItemProvider;

class Product
{
    use IsApiResource;

    public static $manifestMorph = \Lunar\Models\Contracts\Product::class;

    public static function apiResource(): ApiResource
    {
        return new ApiResource(
            operations: [
                new Get(
                    output: Resources\Product::class,
                    provider: ModelManifestItemProvider::class,
                    uriTemplate: '/products/{id}'
                ),
                new GetCollection(
                    output: Resources\Product::class,
                    provider: ModelManifestCollectionProvider::class,
                    uriTemplate: '/products'
                ),
            ],
        );
    }
}
