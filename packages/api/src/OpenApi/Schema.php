<?php

namespace Lunar\Api\OpenApi;

use BackedEnum;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Lunar\Api\Resources\Resource;
use UnitEnum;

/**
 * An immutable JSON Schema fragment. Resources declare field and filter types
 * with it; the generator turns it into the `components.schemas` entries and
 * property schemas of the OpenAPI document.
 */
final class Schema implements JsonSerializable
{
    public const UNTYPED = 'x-lunar-untyped';

    /** Resolved to `#/components/schemas/{name}` by the generator, which knows each resource's label. */
    private ?string $resource = null;

    /** @var array<int, Schema> */
    private array $allOf = [];

    /**
     * @param  array<string, mixed>  $schema
     */
    private function __construct(private array $schema) {}

    public static function string(): self
    {
        return new self(['type' => 'string']);
    }

    public static function integer(): self
    {
        return new self(['type' => 'integer']);
    }

    public static function number(): self
    {
        return new self(['type' => 'number']);
    }

    public static function boolean(): self
    {
        return new self(['type' => 'boolean']);
    }

    public static function dateTime(): self
    {
        return new self(['type' => 'string', 'format' => 'date-time']);
    }

    public static function date(): self
    {
        return new self(['type' => 'string', 'format' => 'date']);
    }

    /** Any JSON value. */
    public static function any(): self
    {
        return new self([]);
    }

    /** The fallback for a field or filter nobody typed; a degraded document, not an error. */
    public static function untyped(): self
    {
        return new self([self::UNTYPED => true]);
    }

    /**
     * @param  class-string<UnitEnum>|array<int, string|int>  $cases  an enum class, or the literal values
     */
    public static function enum(string|array $cases): self
    {
        if (is_string($cases)) {
            if (! enum_exists($cases)) {
                throw new InvalidArgumentException("[{$cases}] is not an enum.");
            }

            $cases = array_map(
                fn (UnitEnum $case) => $case instanceof BackedEnum ? $case->value : $case->name,
                $cases::cases(),
            );
        }

        $values = array_values($cases);
        $type = $values !== [] && array_reduce($values, fn (bool $allInt, mixed $value) => $allInt && is_int($value), true)
            ? 'integer'
            : 'string';

        return new self(['type' => $type, 'enum' => $values]);
    }

    public static function array(Schema $items): self
    {
        return new self(['type' => 'array', 'items' => $items]);
    }

    /**
     * @param  array<string, Schema>  $properties
     * @param  array<int, string>  $required
     */
    public static function object(array $properties, array $required = []): self
    {
        $schema = ['type' => 'object', 'properties' => $properties];

        if ($required !== []) {
            $schema['required'] = array_values($required);
        }

        return new self($schema);
    }

    /** An object with arbitrary keys whose values share one schema. */
    public static function map(Schema $values): self
    {
        return new self(['type' => 'object', 'additionalProperties' => $values]);
    }

    public static function money(): self
    {
        return new self(['$ref' => '#/components/schemas/Money']);
    }

    public static function translations(): self
    {
        return new self(['$ref' => '#/components/schemas/TranslationMap']);
    }

    /**
     * A reference to a registered resource's component schema.
     *
     * @param  class-string<\Lunar\Api\Resources\Resource>  $resource
     */
    public static function ref(string $resource): self
    {
        $schema = new self([]);
        $schema->resource = $resource;

        return $schema;
    }

    /** Every schema must hold: a resource plus the extra properties an endpoint adds. */
    public static function allOf(Schema ...$schemas): self
    {
        $schema = new self([]);
        $schema->allOf = array_values($schemas);

        return $schema;
    }

    public function nullable(bool $nullable = true): self
    {
        $clone = clone $this;
        $clone->schema['x-lunar-nullable'] = $nullable;

        return $clone;
    }

    public function describe(string $description): self
    {
        return $this->with('description', $description);
    }

    public function example(mixed $example): self
    {
        return $this->with('example', $example);
    }

    public function format(string $format): self
    {
        return $this->with('format', $format);
    }

    /**
     * JSON Schema validation keywords: `minimum`, `maxLength`, `default` and so on.
     *
     * @param  array<string, mixed>  $keywords
     */
    public function constrain(array $keywords): self
    {
        $allowed = ['minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum', 'minLength', 'maxLength', 'pattern', 'minItems', 'maxItems', 'uniqueItems', 'default', 'const', 'multipleOf'];

        if ($unknown = array_diff(array_keys($keywords), $allowed)) {
            throw new InvalidArgumentException('Unknown schema keyword(s): '.implode(', ', $unknown).'.');
        }

        $clone = clone $this;
        $clone->schema = array_merge($clone->schema, $keywords);

        return $clone;
    }

    /** Attach an `x-lunar-*` vendor extension. */
    public function extension(string $name, mixed $value): self
    {
        if (! str_starts_with($name, 'x-lunar-')) {
            throw new InvalidArgumentException("Vendor extensions must start with x-lunar-, got [{$name}].");
        }

        return $this->with($name, $value);
    }

    public function isNullable(): bool
    {
        return $this->schema['x-lunar-nullable'] ?? false;
    }

    public function isUntyped(): bool
    {
        return ($this->schema[self::UNTYPED] ?? false) === true;
    }

    /** @return class-string<\Lunar\Api\Resources\Resource>|null */
    public function resource(): ?string
    {
        return $this->resource;
    }

    /**
     * The JSON Schema array. `$componentName` maps a resource class to its
     * component name; without it, references use the class basename.
     *
     * @param  (Closure(class-string<\Lunar\Api\Resources\Resource>): string)|null  $componentName
     * @return array<string, mixed>
     */
    public function toArray(?Closure $componentName = null): array
    {
        $componentName ??= fn (string $class): string => preg_replace('/Resource$/', '', class_basename($class)) ?? $class;

        $nullable = $this->isNullable();
        $schema = $this->schema;
        unset($schema['x-lunar-nullable']);

        if ($this->resource !== null) {
            $schema = ['$ref' => '#/components/schemas/'.$componentName($this->resource)] + $schema;
        }

        if ($this->allOf !== []) {
            $schema['allOf'] = array_map(fn (Schema $item) => $item->toArray($componentName), $this->allOf);
        }

        foreach (['items', 'additionalProperties'] as $key) {
            if (($schema[$key] ?? null) instanceof Schema) {
                $schema[$key] = $schema[$key]->toArray($componentName);
            }
        }

        if (isset($schema['properties'])) {
            $schema['properties'] = array_map(
                fn (Schema|array $property) => $property instanceof Schema ? $property->toArray($componentName) : $property,
                $schema['properties'],
            );
        }

        return $nullable ? self::withNull($schema) : $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private static function withNull(array $schema): array
    {
        if (isset($schema['type'])) {
            $types = array_unique([...(array) $schema['type'], 'null']);
            $schema['type'] = array_values($types);

            if (isset($schema['enum']) && ! in_array(null, $schema['enum'], true)) {
                $schema['enum'][] = null;
            }

            return $schema;
        }

        if (isset($schema['$ref']) || isset($schema['allOf'])) {
            // Keep the annotations beside the reference, not inside the oneOf branch.
            $annotations = array_intersect_key($schema, array_flip(['description', 'example']));
            $reference = array_diff_key($schema, $annotations);

            return $annotations + ['oneOf' => [$reference, ['type' => 'null']]];
        }

        return $schema;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function with(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->schema[$key] = $value;

        return $clone;
    }
}
