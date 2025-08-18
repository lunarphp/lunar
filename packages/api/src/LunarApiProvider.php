<?php

namespace Lunar\Api;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Illuminate\Support\ServiceProvider;

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
