<?php

namespace Lunar\Api;

use Illuminate\Support\ServiceProvider;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;

class LunarApiProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(
            ResourceNameCollectionFactoryInterface::class,
            fn (ResourceNameCollectionFactoryInterface $decorated) => new ResourceNameDecorator($decorated)
        );
    }
}
