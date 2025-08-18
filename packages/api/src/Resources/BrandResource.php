<?php

namespace Lunar\Api\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Lunar\Api\State\BrandCollectionProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/brands',
            provider: BrandCollectionProvider::class,
            paginationEnabled: false
        ),
    ],
    normalizationContext: ['groups' => ['brand:read']]
)]
final class BrandResource
{
    public string $id;

    public string $name;
}
