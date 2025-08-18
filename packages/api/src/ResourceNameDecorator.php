<?php

namespace Lunar\Api;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

final class ResourceNameDecorator implements ResourceNameCollectionFactoryInterface
{
    public function __construct(private ResourceNameCollectionFactoryInterface $decorated) {}

    public function create(): ResourceNameCollection
    {
        $base  = $this->decorated->create();
        $names = iterator_to_array($base->getIterator());

        // Add your DTOs explicitly
        $names[] = \Lunar\Api\Resources\BrandResource::class;

        return new ResourceNameCollection(array_values(array_unique($names)));
    }
}
