<?php

namespace Lunar\Api\OpenApi;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Lunar\Api\Contracts\DescribesSchema;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Http\Controllers\ResourceController;
use Lunar\Api\OpenApi\Attributes\Operation;
use Lunar\Api\OpenApi\Attributes\Responds;
use Lunar\Api\Registry\ResourceDefinition;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Storefront\Http\Middleware\ResolveCustomer;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * One OpenAPI operation per route and method: summary and response from the
 * `Operation` / `Responds` attributes or the `ResourceController` conventions,
 * query parameters from the resource grammar, the body from the form request,
 * security and ability gates from the route middleware.
 */
final class OperationBuilder
{
    public function __construct(
        private readonly SurfaceRegistry $registry,
        private readonly SurfaceProfile $profile,
        private readonly ComponentBuilder $components,
        private readonly Container $container,
        private readonly int $maxIncludeDepth,
    ) {}

    /** The route URI relative to the surface prefix. */
    public function path(Route $route): string
    {
        $uri = trim($route->uri(), '/');
        $prefix = trim($this->profile->prefix, '/');

        if (str_starts_with($uri, $prefix)) {
            $uri = substr($uri, strlen($prefix));
        }

        return '/'.ltrim($uri, '/');
    }

    /** The route name minus the surface prefix. */
    public function relativeName(Route $route): string
    {
        return Str::after((string) $route->getName(), "lunar.api.{$this->profile->surface}.{$this->profile->version}.");
    }

    /** @return array<string, mixed> */
    public function operation(Route $route, string $method): array
    {
        $relative = $this->relativeName($route);
        $path = $this->path($route);
        $tag = Str::before($relative, '.');

        [$reflection, $controller, $action] = $this->reflect($route);

        $operationAttribute = $this->attribute($reflection, Operation::class);
        $responds = $this->attribute($reflection, Responds::class);
        $convention = null;

        if ($responds === null && $controller !== null && is_subclass_of($controller, ResourceController::class) && in_array($action, ['index', 'show'], true)) {
            $resource = (new ReflectionClass($controller))->getDefaultProperties()['resource'] ?? null;

            if (is_string($resource)) {
                $responds = new Responds($resource, many: $action === 'index');
                $convention = $action;
            }
        }

        $definition = $this->definitionFor($responds?->resource);
        $middleware = $route->gatherMiddleware();
        $abilities = $this->abilities($middleware);
        $secured = $this->profile->globalSecurityScheme !== null
            || ($this->profile->customerSecurityScheme !== null && in_array(ResolveCustomer::class, $middleware, true));

        $operation = [
            'operationId' => Str::camel(str_replace('.', ' ', $relative)),
            'tags' => [$tag],
        ];

        if ($summary = $operationAttribute?->summary ?? $this->conventionalSummary($convention, $definition)) {
            $operation['summary'] = $summary;
        }

        if ($operationAttribute?->description) {
            $operation['description'] = $operationAttribute->description;
        }

        if ($relative === 'openapi') {
            // Hidden from docs; untagged so no tag is invented for it.
            unset($operation['tags']);
            $operation['x-hidden'] = true;
            $operation['summary'] ??= 'Retrieve this OpenAPI document';
        }

        $parameters = $this->pathParameters($path, $definition);

        if ($definition !== null && ($convention === 'index' || $responds?->many)) {
            $parameters = [...$parameters, ...$this->collectionParameters($definition)];
        } elseif ($definition !== null && $convention === 'show') {
            $parameters = [...$parameters, ...$this->includeParameters($definition)];
        }

        foreach (array_keys($this->profile->requestHeaders) as $header) {
            $parameters[] = ['$ref' => '#/components/parameters/'.$header];
        }

        if ($parameters !== []) {
            $operation['parameters'] = $parameters;
        }

        $body = in_array($method, ['post', 'put', 'patch'], true) ? $this->requestBody($reflection) : null;

        if ($body !== null) {
            $operation['requestBody'] = $body;
        }

        if ($abilities !== []) {
            $operation['x-lunar-requires'] = $abilities;
        }

        if ($this->profile->customerSecurityScheme !== null && in_array(ResolveCustomer::class, $middleware, true)) {
            $operation['security'] = [[$this->profile->customerSecurityScheme => []]];
        }

        $operation['responses'] = $this->responses($responds, $definition, [
            'secured' => $secured,
            'abilities' => $abilities !== [],
            'addressed' => str_contains($path, '{id}'),
            'validated' => $body !== null || $parameters !== [],
        ]);

        return $operation;
    }

    /**
     * @return array{0: ReflectionFunctionAbstract, 1: class-string|null, 2: string|null}
     */
    private function reflect(Route $route): array
    {
        $uses = $route->getAction('uses');

        if ($uses instanceof Closure) {
            return [new ReflectionFunction($uses), null, null];
        }

        [$class, $method] = array_pad(explode('@', (string) $uses, 2), 2, '__invoke');

        return [new ReflectionMethod($class, $method), $class, $method];
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $attribute
     * @return T|null
     */
    private function attribute(ReflectionFunctionAbstract $reflection, string $attribute): ?object
    {
        $found = $reflection->getAttributes($attribute);

        return $found === [] ? null : $found[0]->newInstance();
    }

    private function definitionFor(?string $resource): ?ResourceDefinition
    {
        if ($resource === null) {
            return null;
        }

        try {
            return $this->registry->definition($resource);
        } catch (ResourceDefinitionException) {
            return null;
        }
    }

    private function conventionalSummary(?string $convention, ?ResourceDefinition $definition): ?string
    {
        if ($convention === null || $definition === null) {
            return null;
        }

        $noun = self::noun($definition->resource::label());

        return $convention === 'index'
            ? "List {$noun}"
            : 'Retrieve '.self::article(Str::singular($noun)).' '.Str::singular($noun);
    }

    /** The label as it reads mid-sentence: lower-cased unless it starts with an acronym. */
    public static function noun(string $label): string
    {
        return preg_match('/^[A-Z]{2,}/', $label) ? $label : Str::lcfirst($label);
    }

    public static function article(string $noun): string
    {
        return in_array(Str::lower(Str::substr($noun, 0, 1)), ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';
    }

    /**
     * @param  array<int, mixed>  $middleware
     * @return array<int, string>
     */
    private function abilities(array $middleware): array
    {
        $abilities = [];

        foreach ($middleware as $entry) {
            if (is_string($entry) && str_starts_with($entry, 'lunar.api.can:')) {
                $abilities[] = Str::before(Str::after($entry, 'lunar.api.can:'), ',');
            }
        }

        return array_values(array_unique($abilities));
    }

    /** @return array<int, array<string, mixed>> */
    private function pathParameters(string $path, ?ResourceDefinition $definition): array
    {
        preg_match_all('/\{(\w+)\??\}/', $path, $matches);
        $parameters = [];

        foreach ($matches[1] as $name) {
            $description = $name === 'id' && $definition !== null
                ? 'The public id of the '.Str::singular(self::noun($definition->resource::label())).'.'
                : ($name === 'id' ? 'The public id of the resource.' : "The {$name} path segment.");

            $parameters[] = [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
                'description' => $description,
            ];
        }

        return $parameters;
    }

    /** @return array<int, array<string, mixed>> */
    private function includeParameters(ResourceDefinition $definition): array
    {
        $includes = $this->includePaths($definition);
        $parameters = [];

        if ($includes !== []) {
            $lines = array_map(fn (array $include) => "- `{$include['path']}`: {$include['description']}", $includes);

            $parameters[] = [
                'name' => 'include',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
                'description' => "Comma-separated related resources to embed, nested with dots up to {$this->maxIncludeDepth} levels.\n\n".implode("\n", $lines),
                'x-lunar-includes' => array_column($includes, 'path'),
            ];
        }

        $properties = [];

        foreach ($this->reachableDefinitions($definition) as $type => $reachable) {
            $names = [...array_keys($reachable->fields()), ...array_keys($reachable->includes())];
            $properties[$type] = [
                'type' => 'string',
                'description' => "Comma-separated fields of {$type} to return: ".implode(', ', $names).'.',
            ];
        }

        $parameters[] = [
            'name' => 'fields',
            'in' => 'query',
            'required' => false,
            'style' => 'deepObject',
            'explode' => true,
            'schema' => ComponentBuilder::normalise(['type' => 'object', 'properties' => $properties]),
            'description' => 'Sparse fieldsets per resource type, for example `fields[products]=name,slug`. `id` and `type` are always returned.',
        ];

        return $parameters;
    }

    /** @return array<int, array<string, mixed>> */
    private function collectionParameters(ResourceDefinition $definition): array
    {
        $parameters = $this->includeParameters($definition);
        $resource = $definition->resource;

        if ($definition->filters() !== []) {
            $properties = [];

            foreach ($definition->filters() as $name => $filter) {
                $value = $this->components->schema(TypeInference::filter($filter, $definition->model()));
                $operators = $filter->allowedOperators();

                if ($operators === ['eq']) {
                    $property = $value;
                } else {
                    $byOperator = [];

                    foreach ($operators as $operator) {
                        $byOperator[$operator] = in_array($operator, ['in', 'not_in'], true)
                            ? ['oneOf' => [['type' => 'array', 'items' => $value], ['type' => 'string', 'description' => 'Comma-separated values.']]]
                            : $value;
                    }

                    $property = ['oneOf' => [
                        $value,
                        ['type' => 'object', 'properties' => $byOperator, 'additionalProperties' => false],
                    ]];
                }

                $property['description'] = trim(($filter->description() ?? '').' Operators: '.implode(', ', $operators).'.');

                if ($filter->abilities() !== []) {
                    $property['x-lunar-requires'] = $filter->abilities();
                }

                $properties[$name] = $property;
            }

            $parameters[] = [
                'name' => 'filter',
                'in' => 'query',
                'required' => false,
                'style' => 'deepObject',
                'explode' => true,
                'schema' => ComponentBuilder::normalise(['type' => 'object', 'properties' => $properties, 'additionalProperties' => false]),
                'description' => 'Filters, for example `filter[handle]=acme` or `filter[price][gte]=1000`. `filter[name]=value` is shorthand for the `eq` operator.',
            ];
        }

        if ($definition->sorts() !== []) {
            $lines = [];

            foreach ($definition->sorts() as $name => $sort) {
                $lines[] = "- `{$name}`".($sort->description() ? ": {$sort->description()}" : '');
            }

            $parameters[] = [
                'name' => 'sort',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
                'description' => "Comma-separated sort keys; prefix a key with `-` to sort descending.\n\n".implode("\n", $lines),
                'x-lunar-sorts' => array_keys($definition->sorts()),
            ];
        }

        $parameters[] = [
            'name' => 'page[number]',
            'in' => 'query',
            'required' => false,
            'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
            'description' => 'The page to return.',
        ];

        $parameters[] = [
            'name' => 'page[size]',
            'in' => 'query',
            'required' => false,
            'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => $resource->maxPageSize(), 'default' => $resource->defaultPageSize()],
            'description' => 'Items per page.',
        ];

        if ($resource->supportsCursorPagination()) {
            $parameters[] = [
                'name' => 'page[cursor]',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
                'description' => 'A cursor from a previous response, instead of page[number].',
            ];
        }

        return $parameters;
    }

    /**
     * Every include path to the configured depth, with the include's description.
     *
     * @return array<int, array{path: string, description: string}>
     */
    private function includePaths(ResourceDefinition $definition, string $prefix = '', int $depth = 1): array
    {
        if ($depth > $this->maxIncludeDepth) {
            return [];
        }

        $paths = [];

        foreach ($definition->includes() as $name => $embed) {
            $path = $prefix.$name;
            $paths[] = ['path' => $path, 'description' => $embed->description() ?? 'Embed '.$name.'.'];

            $target = $this->definitionFor($embed->resource);

            if ($target !== null) {
                $paths = [...$paths, ...$this->includePaths($target, $path.'.', $depth + 1)];
            }
        }

        return $paths;
    }

    /**
     * The definition and every one reachable through includes, keyed by type.
     *
     * @return array<string, ResourceDefinition>
     */
    private function reachableDefinitions(ResourceDefinition $definition): array
    {
        $found = [$definition->type() => $definition];
        $queue = [[$definition, 0]];

        while ($queue !== []) {
            [$current, $depth] = array_shift($queue);

            if ($depth >= $this->maxIncludeDepth) {
                continue;
            }

            foreach ($current->includes() as $embed) {
                $target = $this->definitionFor($embed->resource);

                if ($target !== null && ! isset($found[$target->type()])) {
                    $found[$target->type()] = $target;
                    $queue[] = [$target, $depth + 1];
                }
            }
        }

        return $found;
    }

    /** @return array<string, mixed>|null */
    private function requestBody(ReflectionFunctionAbstract $reflection): ?array
    {
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $class = $type->getName();

            if (! is_subclass_of($class, FormRequest::class)) {
                continue;
            }

            return [
                'required' => true,
                'content' => ['application/json' => ['schema' => ComponentBuilder::normalise($this->components->schema($this->bodySchema($class)))]],
            ];
        }

        return null;
    }

    /**
     * @param  class-string<FormRequest>  $class
     */
    private function bodySchema(string $class): Schema
    {
        if (is_subclass_of($class, DescribesSchema::class)) {
            return $class::schema();
        }

        // Built directly, not through the container: resolving a FormRequest runs its validation.
        $request = new $class;
        $descriptions = method_exists($request, 'descriptions') ? $request->descriptions() : [];

        return RulesToSchema::convert($this->container->call([$request, 'rules']), $descriptions);
    }

    /**
     * @param  array{secured: bool, abilities: bool, addressed: bool, validated: bool}  $flags
     * @return array<string, mixed>
     */
    private function responses(?Responds $responds, ?ResourceDefinition $definition, array $flags): array
    {
        $responses = [];

        if ($responds === null) {
            $responses['200'] = ['description' => 'Success.'];
        } elseif ($responds->isEmpty()) {
            $responses[(string) $responds->status] = ['description' => $responds->description ?? 'Done; no content.'];
        } else {
            $data = $responds->schema !== null
                ? $this->components->schema($responds->schema::schema())
                : $this->components->ref((string) $responds->resource);

            $noun = $definition ? self::noun($definition->resource::label()) : 'resource';
            $description = $responds->description ?? ($responds->many ? "A page of {$noun}." : 'The '.Str::singular($noun).'.');

            $success = [
                'description' => $description,
                'content' => ['application/json' => ['schema' => $this->components->envelope($data, $responds->many, $responds->isPaginated())]],
            ];

            if ($this->profile->responseHeaders !== []) {
                $success['headers'] = array_map(
                    fn (string $header) => ['$ref' => '#/components/headers/'.$header],
                    array_combine(array_keys($this->profile->responseHeaders), array_keys($this->profile->responseHeaders)),
                );
            }

            $responses[(string) $responds->status] = $success;
        }

        if ($flags['secured']) {
            $responses['401'] = ['$ref' => '#/components/responses/Unauthorized'];
        }

        if ($flags['abilities']) {
            $responses['403'] = ['$ref' => '#/components/responses/Forbidden'];
        }

        if ($flags['addressed']) {
            $responses['404'] = ['$ref' => '#/components/responses/NotFound'];
        }

        if ($flags['validated']) {
            $responses['422'] = ['$ref' => '#/components/responses/UnprocessableEntity'];
        }

        $responses['429'] = ['$ref' => '#/components/responses/TooManyRequests'];

        return $responses;
    }
}
