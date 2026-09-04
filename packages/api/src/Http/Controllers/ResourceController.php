<?php

namespace Lunar\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Http\Exceptions\ApiException;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\Query\QueryApplier;
use Lunar\Api\Query\QueryParser;
use Lunar\Api\Registry\ResourceDefinition;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;

/**
 * Shared index/show plumbing: parse the grammar, resolve the resource, run
 * the query, serialise. Surfaces subclass it to supply their registry and
 * the context their principal and locale rules produce.
 */
abstract class ResourceController
{
    /** @var class-string<resource> */
    protected string $resource;

    public function __construct(
        protected ApiManager $api,
        protected QueryParser $parser,
        protected QueryApplier $applier,
    ) {}

    abstract protected function registry(): SurfaceRegistry;

    abstract protected function context(Request $request): SerializationContext;

    /** @return array<string, mixed> */
    protected function meta(Request $request, SerializationContext $context): array
    {
        return [];
    }

    public function index(Request $request): JsonResponse
    {
        $definition = $this->definition();
        $context = $this->context($request);
        $query = $this->parser->parse($request, $definition, $context);
        $context = $context->withQuery($query);

        $builder = $definition->resource->query($context);
        $this->applier->apply($builder, $definition, $query, $context);
        $paginator = $this->applier->paginate($builder, $definition, $query);

        return Envelope::paginated(
            $paginator,
            $definition->serializeMany($paginator->items(), $context),
            $request,
            $this->meta($request, $context),
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        [$model, $context] = $this->find($request, $id);

        return Envelope::item(
            $this->definition()->serialize($model, $context),
            $this->meta($request, $context),
            ['self' => $request->fullUrl()],
        );
    }

    protected function definition(): ResourceDefinition
    {
        return $this->registry()->definition($this->resource);
    }

    /**
     * Resolve the addressed model with the request's includes and fields
     * applied, or fail with a 404 error object.
     *
     * @return array{0: Model, 1: SerializationContext}
     */
    protected function find(Request $request, string $id): array
    {
        $definition = $this->definition();
        $context = $this->context($request);
        $query = $this->parser->parse($request, $definition, $context, collection: false);
        $context = $context->withQuery($query);

        $builder = $definition->resource->scopeIdentifier($definition->resource->query($context), $id);
        $this->applier->applyEagerLoads($builder, $definition, $context);

        $model = $builder->first() ?? throw ApiException::notFound($definition->type(), $id);

        return [$model, $context];
    }
}
