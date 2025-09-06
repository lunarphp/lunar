<?php

namespace Lunar\Api\State;

use ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\PartialPaginator;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerLocatorTrait;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Util\StateOptionsTrait;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\Resources\Product;
use Lunar\Facades\ModelManifest;
use Psr\Container\ContainerInterface;

/**
 * @implements ProviderInterface<Product|null>
 */
class ModelManifestCollectionProvider implements ProviderInterface
{
    use LinksHandlerLocatorTrait;
    use StateOptionsTrait;

    /**
     * @param  LinksHandlerInterface<Model>  $linksHandler
     * @param  iterable<QueryExtensionInterface>  $queryExtensions
     */
    public function __construct(
        private readonly Pagination $pagination,
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

        $model = new (ModelManifest::get($class::$manifestMorph));

        if (! $model) {
            return null;
        }

        if ($handleLinks = $this->getLinksHandler($operation)) {
            $query = $handleLinks($model->query(), $uriVariables, ['operation' => $operation, 'modelClass' => $operation->getClass()] + $context);
        } else {
            $query = $this->linksHandler->handleLinks($model->query(), $uriVariables, ['operation' => $operation, 'modelClass' => $operation->getClass()] + $context);
        }

        foreach ($this->queryExtensions as $extension) {
            $query = $extension->apply($query, $uriVariables, $operation, $context);
        }

        if ($order = $operation->getOrder()) {
            $isList = array_is_list($order);
            foreach ($order as $property => $direction) {
                if ($isList) {
                    $property = $direction;
                    $direction = 'ASC';
                }

                if (str_contains($property, '.')) {
                    [$table, $property] = explode('.', $property);

                    // Relation Order by, we need to do laravel eager loading
                    $query->with([
                        $table => fn ($query) => $query->orderBy($property, $direction),
                    ]);

                    continue;
                }

                $query->orderBy($property, $direction);
            }
        }

        $mapTo = $operation->getOutput()['class'];

        if ($this->pagination->isEnabled($operation, $context) === false) {
            return $query->get()->map(fn ($item) => new $mapTo($item));
        }

        $isPartial = $operation->getPaginationPartial();
        $collection = $query
            ->{$isPartial ? 'simplePaginate' : 'paginate'}(
                perPage: $this->pagination->getLimit($operation, $context),
                page: $this->pagination->getPage($context),
            );

        $collection->setCollection($collection->getCollection()->map(fn ($item) => new $mapTo($item)));

        if ($isPartial) {
            return new PartialPaginator($collection);
        }

        return new Paginator($collection);
    }
}
