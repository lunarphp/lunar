<?php

namespace Lunar\Api\State;

use ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerLocatorTrait;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Util\StateOptionsTrait;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\Resources\Product;
use Lunar\Facades\ModelManifest;
use Psr\Container\ContainerInterface;

/**
 * @implements ProviderInterface<Product|null>
 */
class ModelManifestItemProvider implements ProviderInterface
{
    use LinksHandlerLocatorTrait;
    use StateOptionsTrait;

    /**
     * @param  LinksHandlerInterface<Model>  $linksHandler
     * @param  iterable<QueryExtensionInterface>  $queryExtensions
     */
    public function __construct(
        private readonly LinksHandlerInterface $linksHandler,
        ?ContainerInterface $handleLinksLocator = null,
        private iterable $queryExtensions = [],
    ) {
        $this->handleLinksLocator = $handleLinksLocator;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (! $class = ($context['resource_class'] ?? false)) {
            return null;
        }

        $model = ModelManifest::get($class::$manifestMorph)::find($uriVariables['id']);

        if (! $model) {
            return null;
        }

        if ($handleLinks = $this->getLinksHandler($operation)) {
            $query = $handleLinks($model->query(), $uriVariables, ['operation' => $operation] + $context);
        } else {
            $query = $this->linksHandler->handleLinks($model->query(), $uriVariables, ['operation' => $operation] + $context);
        }

        foreach ($this->queryExtensions as $extension) {
            $query = $extension->apply($query, $uriVariables, $operation, $context);
        }

        $mapTo = $operation->getOutput()['class'];

        return new $mapTo($query->first());
    }
}
