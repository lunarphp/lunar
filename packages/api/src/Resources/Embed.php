<?php

namespace Lunar\Api\Resources;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * A related resource a consumer may embed with `?include=`. Relation includes
 * eager-load the named relation; `make()` includes resolve from data the
 * declared eager loads already put on the model, so neither lazy-loads.
 */
final class Embed
{
    private ?Closure $constrain = null;

    private ?Closure $resolver = null;

    /** @var array<int, string> */
    private array $abilities = [];

    /** @var array<int, string> */
    private array $with = [];

    private ?bool $many = null;

    private ?string $description = null;

    /**
     * @param  class-string<resource>  $resource
     */
    private function __construct(
        public readonly string $name,
        public readonly string $resource,
        private readonly ?string $relation,
    ) {}

    /**
     * Embed an Eloquent relation. `$constrain` receives the relation query and
     * the context: `fn ($query, SerializationContext $context) => ...`.
     *
     * @param  class-string<resource>  $resource
     */
    public static function relation(string $name, string $resource, ?string $relation = null, ?Closure $constrain = null): self
    {
        $include = new self($name, $resource, $relation ?? $name);
        $include->constrain = $constrain;

        return $include;
    }

    /**
     * Embed whatever the closure `(Model $model, SerializationContext $context)`
     * returns: a model, an iterable of models, or null. Declare the relations
     * it reads with `eagerLoad()`.
     *
     * @param  class-string<resource>  $resource
     */
    public static function make(string $name, Closure $resolver, string $resource): self
    {
        $include = new self($name, $resource, null);
        $include->resolver = $resolver;

        return $include;
    }

    /** @param  array<int, string>|string  $relations */
    public function eagerLoad(array|string $relations): self
    {
        $this->with = array_merge($this->with, (array) $relations);

        return $this;
    }

    public function requires(string ...$abilities): self
    {
        $this->abilities = array_merge($this->abilities, $abilities);

        return $this;
    }

    /**
     * Declare that the include resolves to a list. Relation includes read
     * this from the relation type; `make()` includes default to a single
     * resource unless they say otherwise.
     */
    public function many(bool $many = true): self
    {
        $this->many = $many;

        return $this;
    }

    /** The include description in the OpenAPI document. */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** Explicit cardinality, or null to infer it from the relation. */
    public function declaredMany(): ?bool
    {
        return $this->many;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /** @return array<int, string> */
    public function abilities(): array
    {
        return $this->abilities;
    }

    /** The Eloquent relation a relation include reads, or null for `make()` includes. */
    public function eloquentRelation(): ?string
    {
        return $this->relation;
    }

    public function visibleTo(SerializationContext $context): bool
    {
        foreach ($this->abilities as $ability) {
            if (! $context->can($ability)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The relation nested includes descend through: the declared relation, or
     * the single eager load of a `make()` include.
     */
    public function relationName(): ?string
    {
        if ($this->relation !== null) {
            return $this->relation;
        }

        return count($this->with) === 1 ? $this->with[0] : null;
    }

    public function constraint(): ?Closure
    {
        return $this->constrain;
    }

    /**
     * Every relation to eager load for this include.
     *
     * @return array<int, string>
     */
    public function eagerLoads(): array
    {
        return array_values(array_unique(array_filter([$this->relation, ...$this->with])));
    }

    public function resolve(Model $model, SerializationContext $context): mixed
    {
        if ($this->resolver) {
            return ($this->resolver)($model, $context);
        }

        return $model->getRelationValue($this->relation);
    }
}
