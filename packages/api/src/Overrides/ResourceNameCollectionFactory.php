<?php

namespace Lunar\Api\Overrides;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use Lunar\Api\Resources;

class ResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    public function __construct(private ResourceNameCollectionFactoryInterface $decorated) {}

    public function create(): ResourceNameCollection
    {
        // @TODO: this should be opt-in
        $classes = [
            Resources\Brand::class,
            Resources\Product::class,
        ];

        $base = $this->decorated->create();
        $classes = [...$classes, ...iterator_to_array($base->getIterator())];

        return new ResourceNameCollection($classes);
    }
}
