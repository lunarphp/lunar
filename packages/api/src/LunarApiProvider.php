<?php

namespace Lunar\Api;

use ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\ServiceLocator;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Lunar\Api\Overrides\ResourceNameCollectionFactory;
use Lunar\Api\State\ModelManifestCollectionProvider;
use Lunar\Api\State\ModelManifestItemProvider;

class LunarApiProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(
            ResourceNameCollectionFactoryInterface::class,
            fn (ResourceNameCollectionFactoryInterface $decorated) => new ResourceNameCollectionFactory($decorated)
        );

        $this->app->singleton(ModelManifestItemProvider::class, function (Application $app) {
            $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

            return new ModelManifestItemProvider(new LinksHandler($app, $app->make(ResourceMetadataCollectionFactoryInterface::class)), new ServiceLocator($tagged), $app->tagged(QueryExtensionInterface::class));
        });

        $this->app->tag(ModelManifestItemProvider::class, ProviderInterface::class);

        $this->app->singleton(ModelManifestCollectionProvider::class, function (Application $app) {
            $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

            return new ModelManifestCollectionProvider($app->make(Pagination::class), new LinksHandler($app, $app->make(ResourceMetadataCollectionFactoryInterface::class)), new ServiceLocator($tagged), $app->tagged(QueryExtensionInterface::class));
        });

        $this->app->tag(ModelManifestCollectionProvider::class, ProviderInterface::class);
    }
}
