<?php

namespace Lunar\Api\Resources;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\OpenApi\Schema;

/**
 * A registered `?filter[name]` (optionally `?filter[name][operator]`). Only
 * registered filters and operators are accepted, so a consumer cannot query
 * an arbitrary column.
 */
final class Filter
{
    public const OPERATORS = ['eq', 'ne', 'gt', 'gte', 'lt', 'lte', 'in', 'not_in', 'like'];

    /** @var array<int, string> */
    private array $operators = ['eq'];

    /** @var array<int, string> */
    private array $abilities = [];

    private ?Schema $type = null;

    private ?string $description = null;

    /** The column a `column()` / `exact()` filter compares, for type inference. */
    private ?string $column = null;

    private bool $scope = false;

    private function __construct(public readonly string $name, private readonly Closure $apply) {}

    /** Match a column exactly; accepts `eq`, `ne`, `in` and `not_in`. */
    public static function exact(string $name, ?string $column = null): self
    {
        $column ??= $name;

        return self::column($name, $column)->operators(['eq', 'ne', 'in', 'not_in']);
    }

    /** Compare a column with every operator. */
    public static function column(string $name, ?string $column = null): self
    {
        $column ??= $name;

        $filter = new self($name, function (Builder $query, mixed $value, string $operator) use ($column): void {
            self::applyToColumn($query, $query->qualifyColumn($column), $value, $operator);
        });
        $filter->column = $column;

        return $filter->operators(self::OPERATORS);
    }

    /**
     * A filter applied by `fn (Builder $query, mixed $value, string $operator, SerializationContext $context)`.
     */
    public static function make(string $name, Closure $apply): self
    {
        return new self($name, $apply);
    }

    /**
     * Call a native or registered local scope (`Builder::registerScope()`).
     * A boolean-ish value calls the scope with no arguments (or skips it when
     * false); any other value is passed through as the scope's argument.
     */
    public static function scope(string $name, ?string $scope = null): self
    {
        $scope ??= $name;

        $filter = new self($name, function (Builder $query, mixed $value) use ($scope): void {
            if (in_array($value, [false, 'false', '0', 0], true)) {
                return;
            }

            if (in_array($value, [true, 'true', '1', 1, ''], true)) {
                $query->{$scope}();

                return;
            }

            $query->{$scope}($value);
        });
        $filter->scope = true;

        return $filter;
    }

    /** @param  array<int, string>  $operators */
    public function operators(array $operators): self
    {
        $this->operators = array_values($operators);

        return $this;
    }

    public function requires(string ...$abilities): self
    {
        $this->abilities = array_merge($this->abilities, $abilities);

        return $this;
    }

    /**
     * The type of the filter value for the OpenAPI document. Column filters
     * infer it from the model's casts and scope filters default to boolean;
     * `make()` filters must declare it.
     */
    public function type(Schema $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** The parameter description in the OpenAPI document. */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function declaredType(): ?Schema
    {
        return $this->type;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /** The column a `column()` or `exact()` filter compares, or null for scope and `make()` filters. */
    public function comparedColumn(): ?string
    {
        return $this->column;
    }

    public function isScope(): bool
    {
        return $this->scope;
    }

    /** @return array<int, string> */
    public function abilities(): array
    {
        return $this->abilities;
    }

    /** @return array<int, string> */
    public function allowedOperators(): array
    {
        return $this->operators;
    }

    public function allows(string $operator): bool
    {
        return in_array($operator, $this->operators, true);
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
     * @param  Builder<Model>  $query
     */
    public function apply(Builder $query, mixed $value, string $operator, SerializationContext $context): void
    {
        ($this->apply)($query, $value, $operator, $context);
    }

    /**
     * Comma-separated values become a list for `in` / `not_in`.
     *
     * @return array<int, string>
     */
    public static function listValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($item) => $item !== ''));
    }

    /**
     * @param  Builder<Model>  $query
     */
    public static function applyToColumn(Builder $query, string $column, mixed $value, string $operator): void
    {
        match ($operator) {
            'eq' => $query->where($column, '=', $value),
            'ne' => $query->where($column, '!=', $value),
            'gt' => $query->where($column, '>', $value),
            'gte' => $query->where($column, '>=', $value),
            'lt' => $query->where($column, '<', $value),
            'lte' => $query->where($column, '<=', $value),
            'in' => $query->whereIn($column, self::listValue($value)),
            'not_in' => $query->whereNotIn($column, self::listValue($value)),
            'like' => $query->where($column, 'like', '%'.$value.'%'),
        };
    }
}
