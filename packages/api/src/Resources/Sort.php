<?php

namespace Lunar\Api\Resources;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A registered `?sort=` key. `-name` sorts descending.
 */
final class Sort
{
    /** @var array<int, string> */
    private array $abilities = [];

    private ?string $description = null;

    private function __construct(public readonly string $name, private readonly Closure $apply) {}

    public static function column(string $name, ?string $column = null): self
    {
        $column ??= $name;

        return new self($name, function (Builder $query, string $direction) use ($column): void {
            $query->orderBy($query->qualifyColumn($column), $direction);
        });
    }

    /**
     * A sort applied by `fn (Builder $query, string $direction, SerializationContext $context)`.
     */
    public static function make(string $name, Closure $apply): self
    {
        return new self($name, $apply);
    }

    public function requires(string ...$abilities): self
    {
        $this->abilities = array_merge($this->abilities, $abilities);

        return $this;
    }

    /** How the sort is described in the OpenAPI document. */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
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
    public function apply(Builder $query, string $direction, SerializationContext $context): void
    {
        ($this->apply)($query, $direction, $context);
    }
}
