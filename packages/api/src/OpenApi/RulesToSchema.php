<?php

namespace Lunar\Api\OpenApi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;
use ReflectionProperty;
use Stringable;

/**
 * Best-effort conversion of a form request's `rules()` into an object schema.
 * Type, format, nullability, enum and size rules map; conditional rules and
 * custom rule objects are ignored, so a request can override the result by
 * implementing `DescribesSchema` or refine it with `descriptions()`.
 */
final class RulesToSchema
{
    /**
     * @param  array<string, mixed>  $rules  as `rules()` returns them
     * @param  array<string, string>  $descriptions  keyed by field name, dotted for nested fields
     */
    public static function convert(array $rules, array $descriptions = []): Schema
    {
        $root = ['rules' => [], 'children' => [], 'items' => null];

        foreach ($rules as $key => $set) {
            $node = &$root;

            foreach (explode('.', (string) $key) as $segment) {
                if ($segment === '*') {
                    $node['items'] ??= ['rules' => [], 'children' => [], 'items' => null];
                    $node = &$node['items'];

                    continue;
                }

                $node['children'][$segment] ??= ['rules' => [], 'children' => [], 'items' => null];
                $node = &$node['children'][$segment];
            }

            $node['rules'] = self::normalise($set);
            unset($node);
        }

        return self::schemaFor($root, '', $descriptions, forceObject: true);
    }

    /**
     * @param  array{rules: array<int, mixed>, children: array<string, mixed>, items: array<string, mixed>|null}  $node
     * @param  array<string, string>  $descriptions
     */
    private static function schemaFor(array $node, string $path, array $descriptions, bool $forceObject = false): Schema
    {
        $rules = $node['rules'];
        $names = self::ruleNames($rules);

        if ($forceObject || $node['children'] !== []) {
            $properties = [];
            $required = [];

            foreach ($node['children'] as $name => $child) {
                $childPath = $path === '' ? $name : "{$path}.{$name}";
                $properties[$name] = self::schemaFor($child, $childPath, $descriptions);

                if (array_key_exists('required', self::ruleNames($child['rules']))) {
                    $required[] = $name;
                }
            }

            $schema = Schema::object($properties, $required);
        } elseif ($node['items'] !== null || isset($names['array']) || isset($names['list'])) {
            $items = $node['items'] !== null ? self::schemaFor($node['items'], "{$path}.*", $descriptions) : Schema::any();
            $schema = Schema::array($items)->constrain(self::sizeConstraints($rules, 'array'));
        } else {
            $schema = self::scalar($rules, $names);
        }

        if (isset($names['nullable'])) {
            $schema = $schema->nullable();
        }

        if (isset($descriptions[$path])) {
            $schema = $schema->describe($descriptions[$path]);
        }

        return $schema;
    }

    /**
     * @param  array<int, mixed>  $rules
     * @param  array<string, string>  $names  rule name => parameter string
     */
    private static function scalar(array $rules, array $names): Schema
    {
        foreach ($rules as $rule) {
            if ($rule instanceof Enum) {
                return Schema::enum(self::property($rule, 'type'));
            }

            if ($rule instanceof In) {
                return Schema::enum(array_values(self::property($rule, 'values')));
            }
        }

        if (isset($names['in'])) {
            return Schema::enum(str_getcsv($names['in'], ',', '"', '\\'));
        }

        $kind = match (true) {
            isset($names['integer']) => 'integer',
            isset($names['numeric']) => 'number',
            isset($names['boolean']), isset($names['accepted']) => 'boolean',
            isset($names['string']), isset($names['email']), isset($names['url']), isset($names['uuid']), isset($names['ulid']), isset($names['date']), isset($names['date_format']) => 'string',
            default => null,
        };

        $schema = match ($kind) {
            'integer' => Schema::integer(),
            'number' => Schema::number(),
            'boolean' => Schema::boolean(),
            'string' => Schema::string(),
            default => Schema::any(),
        };

        if ($kind === 'string') {
            $format = match (true) {
                isset($names['email']) => 'email',
                isset($names['url']) => 'uri',
                isset($names['uuid']) => 'uuid',
                isset($names['date']) => 'date-time',
                default => null,
            };

            if ($format) {
                $schema = $schema->format($format);
            }
        }

        return $kind === null ? $schema : $schema->constrain(self::sizeConstraints($rules, $kind));
    }

    /**
     * `min`, `max`, `between`, `size` and `digits` as the keywords the type uses.
     *
     * @param  array<int, mixed>  $rules
     * @return array<string, int|float>
     */
    private static function sizeConstraints(array $rules, string $kind): array
    {
        [$minKey, $maxKey] = match ($kind) {
            'integer', 'number' => ['minimum', 'maximum'],
            'string' => ['minLength', 'maxLength'],
            'array' => ['minItems', 'maxItems'],
            default => [null, null],
        };

        if ($minKey === null) {
            return [];
        }

        $constraints = [];

        foreach (self::ruleNames($rules) as $name => $parameters) {
            $values = array_map(fn (string $value) => $value + 0, array_filter(explode(',', $parameters), 'is_numeric'));

            match ($name) {
                'min' => $constraints[$minKey] = $values[0] ?? null,
                'max' => $constraints[$maxKey] = $values[0] ?? null,
                'between' => [$constraints[$minKey], $constraints[$maxKey]] = [$values[0] ?? null, $values[1] ?? null],
                'size', 'digits' => [$constraints[$minKey], $constraints[$maxKey]] = [$values[0] ?? null, $values[0] ?? null],
                default => null,
            };
        }

        return array_filter($constraints, fn ($value) => $value !== null);
    }

    /**
     * String rules as `name => parameters`; rule objects are kept aside for `scalar()`.
     *
     * @param  array<int, mixed>  $rules
     * @return array<string, string>
     */
    private static function ruleNames(array $rules): array
    {
        $names = [];

        foreach ($rules as $rule) {
            if ($rule instanceof ValidationRule || $rule instanceof Enum || $rule instanceof In) {
                continue;
            }

            if (! is_string($rule) && ! $rule instanceof Stringable) {
                continue;
            }

            [$name, $parameters] = array_pad(explode(':', (string) $rule, 2), 2, '');
            $names[strtolower($name)] = $parameters;
        }

        return $names;
    }

    /** @return array<int, mixed> */
    private static function normalise(mixed $set): array
    {
        if (is_string($set)) {
            return explode('|', $set);
        }

        return is_array($set) ? array_values($set) : [$set];
    }

    private static function property(object $rule, string $name): mixed
    {
        $property = new ReflectionProperty($rule, $name);

        return $property->getValue($rule);
    }
}
