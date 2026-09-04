<?php

namespace Lunar\Api\Registry;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Normalizer;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\ResourceExtension;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;

/**
 * A resource with its extensions merged: the effective set of fields,
 * includes, filters and sorts the surface serves for one wire type.
 */
final class ResourceDefinition
{
    /** @var array<string, Field> */
    private array $fields = [];

    /** @var array<string, Embed> */
    private array $includes = [];

    /** @var array<string, Filter> */
    private array $filters = [];

    /** @var array<string, Sort> */
    private array $sorts = [];

    /** @var array<int|string, mixed> */
    private array $eagerLoad = [];

    /** @var array<int, Closure> */
    private array $routes = [];

    /**
     * @param  class-string<resource>  $key  the built-in class extensions are keyed by
     * @param  array<int, ResourceExtension>  $extensions
     */
    public function __construct(
        public readonly Resource $resource,
        public readonly string $key,
        array $extensions = [],
    ) {
        $this->absorb($resource::class, $resource->fields(), $resource->includes(), $resource->filters(), $resource->sorts(), $resource->eagerLoad());

        foreach ($extensions as $extension) {
            $this->absorb($extension::class, $extension->fields(), $extension->includes(), $extension->filters(), $extension->sorts(), $extension->eagerLoad());

            if ($routes = $extension->routes()) {
                $this->routes[] = $routes;
            }
        }
    }

    /**
     * @param  array<int, Field>  $fields
     * @param  array<int, Embed>  $includes
     * @param  array<int, Filter>  $filters
     * @param  array<int, Sort>  $sorts
     * @param  array<int|string, mixed>  $eagerLoad
     */
    private function absorb(string $source, array $fields, array $includes, array $filters, array $sorts, array $eagerLoad): void
    {
        foreach ($fields as $field) {
            if (isset($this->fields[$field->name]) || isset($this->includes[$field->name])) {
                throw new ResourceDefinitionException("{$source} declares field [{$field->name}] which [{$this->type()}] already has.");
            }

            $this->fields[$field->name] = $field;
        }

        foreach ($includes as $include) {
            if (isset($this->includes[$include->name]) || isset($this->fields[$include->name])) {
                throw new ResourceDefinitionException("{$source} declares include [{$include->name}] which [{$this->type()}] already has.");
            }

            $this->includes[$include->name] = $include;
        }

        foreach ($filters as $filter) {
            if (isset($this->filters[$filter->name])) {
                throw new ResourceDefinitionException("{$source} declares filter [{$filter->name}] which [{$this->type()}] already has.");
            }

            $this->filters[$filter->name] = $filter;
        }

        foreach ($sorts as $sort) {
            if (isset($this->sorts[$sort->name])) {
                throw new ResourceDefinitionException("{$source} declares sort [{$sort->name}] which [{$this->type()}] already has.");
            }

            $this->sorts[$sort->name] = $sort;
        }

        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);
    }

    public function type(): string
    {
        return $this->resource::type();
    }

    /** @return class-string<Model> */
    public function model(): string
    {
        return $this->resource::model();
    }

    /** @return array<string, Field> */
    public function fields(): array
    {
        return $this->fields;
    }

    public function field(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }

    /** @return array<string, Embed> */
    public function includes(): array
    {
        return $this->includes;
    }

    public function embed(string $name): ?Embed
    {
        return $this->includes[$name] ?? null;
    }

    /** @return array<string, Filter> */
    public function filters(): array
    {
        return $this->filters;
    }

    public function filter(string $name): ?Filter
    {
        return $this->filters[$name] ?? null;
    }

    /** @return array<string, Sort> */
    public function sorts(): array
    {
        return $this->sorts;
    }

    public function sort(string $name): ?Sort
    {
        return $this->sorts[$name] ?? null;
    }

    /** @return array<int|string, mixed> */
    public function eagerLoad(): array
    {
        return $this->eagerLoad;
    }

    /** @return array<int, Closure> */
    public function extensionRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Field and include names a sparse fieldset may name.
     *
     * @return array<int, string>
     */
    public function selectableNames(SerializationContext $context): array
    {
        $names = [];

        foreach ($this->fields as $name => $field) {
            if ($field->visibleTo($context)) {
                $names[] = $name;
            }
        }

        foreach ($this->includes as $name => $include) {
            if ($include->visibleTo($context)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /** @return array<string, mixed> */
    public function serialize(Model $model, SerializationContext $context): array
    {
        $data = [
            'id' => $this->resource->identifier($model),
            'type' => $this->type(),
        ];

        $requested = $context->fieldsFor($this->type());

        foreach ($this->fields as $name => $field) {
            if ($requested !== null && ! in_array($name, $requested, true)) {
                continue;
            }

            if (! $field->visibleTo($context)) {
                continue;
            }

            $data[$name] = Normalizer::normalize($field->resolve($model, $context), $context);
        }

        foreach ($context->includes->names() as $name) {
            $include = $this->includes[$name] ?? null;

            if (! $include || ! $include->visibleTo($context)) {
                continue;
            }

            $related = $include->resolve($model, $context);
            $definition = $context->registry->definition($include->resource);
            $child = $context->descend($name);

            $data[$name] = match (true) {
                $related === null => null,
                $related instanceof Model => $definition->serialize($related, $child),
                default => $definition->serializeMany($related, $child),
            };
        }

        return $data;
    }

    /**
     * @param  iterable<Model>  $models
     * @return array<int, array<string, mixed>>
     */
    public function serializeMany(iterable $models, SerializationContext $context): array
    {
        $out = [];

        foreach ($models as $model) {
            $out[] = $this->serialize($model, $context);
        }

        return $out;
    }

    /**
     * The introspection shape served by `_schema`, honouring the caller's abilities.
     *
     * @return array<string, mixed>
     */
    public function schema(SerializationContext $context): array
    {
        return [
            'type' => $this->type(),
            'fields' => array_values(array_map(
                fn (Field $field) => $field->toSchema(),
                array_filter($this->fields, fn (Field $field) => $field->visibleTo($context)),
            )),
            'includes' => array_values(array_map(
                fn (Embed $include) => $include->toSchema(),
                array_filter($this->includes, fn (Embed $include) => $include->visibleTo($context)),
            )),
            'filters' => array_values(array_map(
                fn (Filter $filter) => $filter->toSchema(),
                array_filter($this->filters, fn (Filter $filter) => $filter->visibleTo($context)),
            )),
            'sorts' => array_values(array_map(
                fn (Sort $sort) => $sort->toSchema(),
                array_filter($this->sorts, fn (Sort $sort) => $sort->visibleTo($context)),
            )),
            'pagination' => [
                'default_size' => $this->resource->defaultPageSize(),
                'max_size' => $this->resource->maxPageSize(),
                'cursor' => $this->resource->supportsCursorPagination(),
            ],
        ];
    }
}
