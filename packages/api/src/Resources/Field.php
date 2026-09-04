<?php

namespace Lunar\Api\Resources;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * One attribute of a serialised resource. Built with `make()` and a closure
 * so the package and add-ons declare fields the same way.
 */
final class Field
{
    private ?Closure $resolver = null;

    /** @var array<int|string, mixed> */
    private array $with = [];

    /** @var array<int|string, mixed> */
    private array $withCount = [];

    /** @var array<string, string> relation => column */
    private array $withAvg = [];

    /** @var array<string, string> */
    private array $withSum = [];

    /** @var array<string, string> */
    private array $withMin = [];

    /** @var array<string, string> */
    private array $withMax = [];

    /** @var array<int, string> */
    private array $abilities = [];

    private bool $translatable = false;

    private ?string $attribute = null;

    private function __construct(public readonly string $name) {}

    /**
     * A field resolved by the closure `(Model $model, SerializationContext $context)`,
     * or by the model attribute of the same name when no closure is given.
     */
    public static function make(string $name, ?Closure $resolver = null): self
    {
        $field = new self($name);
        $field->resolver = $resolver;

        return $field;
    }

    /**
     * A translatable attribute: the resolved string on surfaces that localise
     * (storefront), the full locale map on surfaces that do not (admin).
     */
    public static function translatable(string $name, ?string $attribute = null): self
    {
        $field = new self($name);
        $field->translatable = true;
        $field->attribute = $attribute ?? $name;

        return $field;
    }

    /**
     * Relations and aggregates to load on index/show so resolving the field
     * never lazy-loads. Aggregates are `relation => column` maps.
     *
     * @param  array<int|string, mixed>|string  $with
     * @param  array<int|string, mixed>  $withCount
     * @param  array<string, string>  $withAvg
     * @param  array<string, string>  $withSum
     * @param  array<string, string>  $withMin
     * @param  array<string, string>  $withMax
     */
    public function eagerLoad(
        array|string $with = [],
        array $withCount = [],
        array $withAvg = [],
        array $withSum = [],
        array $withMin = [],
        array $withMax = [],
    ): self {
        $this->with = array_merge($this->with, (array) $with);
        $this->withCount = array_merge($this->withCount, $withCount);
        $this->withAvg = array_merge($this->withAvg, $withAvg);
        $this->withSum = array_merge($this->withSum, $withSum);
        $this->withMin = array_merge($this->withMin, $withMin);
        $this->withMax = array_merge($this->withMax, $withMax);

        return $this;
    }

    /** Abilities the principal needs for the field to appear; otherwise it is dropped. */
    public function requires(string ...$abilities): self
    {
        $this->abilities = array_merge($this->abilities, $abilities);

        return $this;
    }

    /** @return array<int, string> */
    public function abilities(): array
    {
        return $this->abilities;
    }

    public function isTranslatable(): bool
    {
        return $this->translatable;
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

    public function resolve(Model $model, SerializationContext $context): mixed
    {
        if ($this->translatable) {
            if ($context->translations === Translations::Resolved) {
                return $model->translate($this->attribute, $context->locale());
            }

            $value = $model->getAttribute($this->attribute);

            return $value instanceof Collection ? $value->all() : $value;
        }

        if ($this->resolver) {
            return ($this->resolver)($model, $context);
        }

        return $model->getAttribute($this->name);
    }

    /**
     * @param  Builder<Model>|Relation<Model, Model, mixed>  $query
     */
    public function applyEagerLoads(Builder|Relation $query): void
    {
        if ($this->with !== []) {
            $query->with($this->with);
        }

        if ($this->withCount !== []) {
            $query->withCount($this->withCount);
        }

        foreach ($this->withAvg as $relation => $column) {
            $query->withAvg($relation, $column);
        }

        foreach ($this->withSum as $relation => $column) {
            $query->withSum($relation, $column);
        }

        foreach ($this->withMin as $relation => $column) {
            $query->withMin($relation, $column);
        }

        foreach ($this->withMax as $relation => $column) {
            $query->withMax($relation, $column);
        }
    }

    /** @return array{name: string, translatable: bool, requires: array<int, string>} */
    public function toSchema(): array
    {
        return [
            'name' => $this->name,
            'translatable' => $this->translatable,
            'requires' => $this->abilities,
        ];
    }
}
