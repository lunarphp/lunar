<?php

namespace Lunar\Api\Resources;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

        return new self($name, function (Builder $query, mixed $value) use ($scope): void {
            if (in_array($value, [false, 'false', '0', 0], true)) {
                return;
            }

            if (in_array($value, [true, 'true', '1', 1, ''], true)) {
                $query->{$scope}();

                return;
            }

            $query->{$scope}($value);
        });
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

    /** @return array{name: string, operators: array<int, string>, requires: array<int, string>} */
    public function toSchema(): array
    {
        return [
            'name' => $this->name,
            'operators' => $this->operators,
            'requires' => $this->abilities,
        ];
    }
}
