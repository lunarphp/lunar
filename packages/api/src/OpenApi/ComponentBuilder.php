<?php

namespace Lunar\Api\OpenApi;

use Illuminate\Support\Str;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Registry\ResourceDefinition;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\Resource;
use stdClass;

/**
 * The `components` of a surface document: one schema per resource, the
 * shared wire shapes (Money, TranslationMap, errors, pagination), the
 * surface's header parameters, error responses and security schemes. Also
 * the envelopes operations wrap their data in.
 */
final class ComponentBuilder
{
    public function __construct(
        private readonly SurfaceRegistry $registry,
        private readonly SurfaceProfile $profile,
    ) {}

    /**
     * `products` is `Product`, `collection-groups` is `CollectionGroup`. Classes
     * not registered on the surface fall back to their basename.
     *
     * @param  class-string<\Lunar\Api\Resources\Resource>  $resource
     */
    public function componentName(string $resource): string
    {
        try {
            return self::nameForType($this->registry->definition($resource)->type());
        } catch (ResourceDefinitionException) {
            return (string) preg_replace('/Resource$/', '', class_basename($resource));
        }
    }

    public static function nameForType(string $type): string
    {
        return Str::studly(Str::singular($type));
    }

    /** @return array{'$ref': string} */
    public function ref(string $resource): array
    {
        return ['$ref' => '#/components/schemas/'.$this->componentName($resource)];
    }

    /** @return array<string, mixed> */
    public function schema(Schema $schema): array
    {
        return $schema->toArray(fn (string $class) => $this->componentName($class));
    }

    /** @return array<string, mixed> */
    public function components(): array
    {
        $schemas = [];

        foreach ($this->registry->definitions() as $definition) {
            $schemas[self::nameForType($definition->type())] = $this->resourceSchema($definition);
        }

        $schemas += $this->sharedSchemas();

        $components = ['schemas' => array_map(self::normalise(...), $schemas)];

        if ($this->profile->requestHeaders !== []) {
            $components['parameters'] = $this->profile->requestHeaders;
        }

        if ($this->profile->responseHeaders !== []) {
            $components['headers'] = $this->profile->responseHeaders;
        }

        $components['responses'] = $this->errorResponses();

        if ($this->profile->securitySchemes !== []) {
            $components['securitySchemes'] = $this->profile->securitySchemes;
        }

        return $components;
    }

    /** @return array<string, mixed> */
    public function resourceSchema(ResourceDefinition $definition): array
    {
        $label = $definition->resource::label();
        $singular = Str::lower(Str::singular($label));

        $properties = [
            'id' => ['type' => 'string', 'description' => "The public id of the {$singular}."],
            'type' => ['type' => 'string', 'const' => $definition->type(), 'description' => 'The resource type.'],
        ];
        $required = ['id', 'type'];

        foreach ($definition->fields() as $name => $field) {
            $schema = TypeInference::field($field, $definition->model(), $this->profile->translations);
            $property = $this->schema($schema);

            if ($field->description() !== null) {
                $property['description'] = $field->description();
            }

            if ($field->abilities() !== []) {
                $property['x-lunar-requires'] = $field->abilities();
            }

            $properties[$name] = $property;

            if (! $schema->isNullable()) {
                $required[] = $name;
            }
        }

        foreach ($definition->includes() as $name => $embed) {
            $target = $this->ref($embed->resource);
            $property = TypeInference::many($embed, $definition->model())
                ? ['type' => 'array', 'items' => $target]
                : ['oneOf' => [$target, ['type' => 'null']]];

            $property['description'] = trim(($embed->description() ?? '').' Present when included.');

            if ($embed->abilities() !== []) {
                $property['x-lunar-requires'] = $embed->abilities();
            }

            $properties[$name] = $property;
        }

        $schema = ['type' => 'object', 'title' => Str::singular($label)];

        if ($definition->resource::description() !== '') {
            $schema['description'] = $definition->resource::description();
        }

        return $schema + [
            'properties' => $properties,
            'required' => $required,
            'x-lunar-type' => $definition->type(),
        ];
    }

    /**
     * The `data` / `meta` / `links` envelope around a response body.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function envelope(array $data, bool $many = false, bool $paginated = false): array
    {
        $properties = ['data' => $many ? ['type' => 'array', 'items' => $data] : $data];
        $required = ['data'];

        $meta = $this->profile->metaProperties;

        if ($paginated) {
            $meta['pagination'] = ['$ref' => '#/components/schemas/PaginationMeta'];
        }

        if ($meta !== []) {
            $properties['meta'] = ['type' => 'object', 'properties' => $meta, 'required' => array_keys($meta)];
            $required[] = 'meta';
        }

        $properties['links'] = ['$ref' => '#/components/schemas/Links'];

        if ($paginated) {
            $required[] = 'links';
        }

        return self::normalise(['type' => 'object', 'properties' => $properties, 'required' => $required]);
    }

    /**
     * JSON encodes an empty PHP array as `[]`; an empty schema or property
     * map must be `{}`. Walks the schema positions and swaps them for objects.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>|stdClass
     */
    public static function normalise(array $schema): array|stdClass
    {
        foreach (['items', 'additionalProperties', 'not'] as $key) {
            if (isset($schema[$key]) && is_array($schema[$key])) {
                $schema[$key] = self::normalise($schema[$key]);
            }
        }

        foreach (['oneOf', 'anyOf', 'allOf'] as $key) {
            if (isset($schema[$key]) && is_array($schema[$key])) {
                $schema[$key] = array_map(self::normalise(...), $schema[$key]);
            }
        }

        if (array_key_exists('properties', $schema) && is_array($schema['properties'])) {
            $schema['properties'] = $schema['properties'] === []
                ? new stdClass
                : array_map(self::normalise(...), $schema['properties']);
        }

        return $schema === [] ? new stdClass : $schema;
    }

    /** @return array<string, array<string, mixed>> */
    private function sharedSchemas(): array
    {
        return [
            'Money' => [
                'type' => 'object',
                'title' => 'Money',
                'description' => 'A monetary amount in minor units with its currency, so arithmetic never needs to know the decimal places.',
                'properties' => [
                    'amount' => ['type' => 'integer', 'description' => 'The amount in minor units (pence, cents).'],
                    'currency' => ['type' => 'string', 'description' => 'ISO 4217 currency code.'],
                    'decimal_places' => ['type' => 'integer', 'description' => 'Minor units per major unit, as a power of ten.'],
                    'formatted' => ['type' => ['string', 'null'], 'description' => 'The amount formatted for the request locale.'],
                ],
                'required' => ['amount', 'currency', 'decimal_places', 'formatted'],
            ],
            'TranslationMap' => [
                'type' => 'object',
                'title' => 'TranslationMap',
                'description' => 'A translatable value: one entry per locale code.',
                'additionalProperties' => ['type' => ['string', 'null']],
            ],
            'Error' => [
                'type' => 'object',
                'title' => 'Error',
                'description' => 'One JSON:API error object.',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'The HTTP status, as a string.'],
                    'code' => ['type' => 'string', 'description' => 'A stable machine-readable code, such as unknown_filter or validation_failed.'],
                    'title' => ['type' => 'string', 'description' => 'A short human-readable summary.'],
                    'detail' => ['type' => 'string', 'description' => 'What went wrong for this occurrence.'],
                    'source' => [
                        'type' => 'object',
                        'description' => 'Where the error originated: a query parameter, a JSON pointer into the body, or a header.',
                        'properties' => [
                            'parameter' => ['type' => 'string'],
                            'pointer' => ['type' => 'string'],
                            'header' => ['type' => 'string'],
                        ],
                    ],
                ],
                'required' => ['status', 'code', 'title'],
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'title' => 'ErrorResponse',
                'description' => 'Every error response: one or more error objects.',
                'properties' => [
                    'errors' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Error']],
                ],
                'required' => ['errors'],
            ],
            'PaginationMeta' => [
                'title' => 'PaginationMeta',
                'description' => 'Page-number pagination, or cursor pagination when the request used page[cursor].',
                'oneOf' => [
                    [
                        'type' => 'object',
                        'title' => 'PagePagination',
                        'properties' => [
                            'page' => ['type' => 'integer', 'description' => 'The current page, from 1.'],
                            'per_page' => ['type' => 'integer', 'description' => 'Items per page.'],
                            'total' => ['type' => 'integer', 'description' => 'Total matching items.'],
                            'last_page' => ['type' => 'integer', 'description' => 'The last page number.'],
                        ],
                        'required' => ['page', 'per_page', 'total', 'last_page'],
                    ],
                    [
                        'type' => 'object',
                        'title' => 'CursorPagination',
                        'properties' => [
                            'per_page' => ['type' => 'integer', 'description' => 'Items per page.'],
                            'next_cursor' => ['type' => ['string', 'null'], 'description' => 'Cursor for the next page, or null on the last.'],
                            'prev_cursor' => ['type' => ['string', 'null'], 'description' => 'Cursor for the previous page, or null on the first.'],
                        ],
                        'required' => ['per_page', 'next_cursor', 'prev_cursor'],
                    ],
                ],
            ],
            'Links' => [
                'type' => 'object',
                'title' => 'Links',
                'description' => 'Navigation links. Only self is present on item responses.',
                'properties' => [
                    'self' => ['type' => 'string', 'format' => 'uri', 'description' => 'The URL of this response.'],
                    'first' => ['type' => ['string', 'null'], 'format' => 'uri'],
                    'last' => ['type' => ['string', 'null'], 'format' => 'uri'],
                    'next' => ['type' => ['string', 'null'], 'format' => 'uri'],
                    'prev' => ['type' => ['string', 'null'], 'format' => 'uri'],
                ],
                'required' => ['self'],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function errorResponses(): array
    {
        $error = fn (string $description): array => [
            'description' => $description,
            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]],
        ];

        return [
            'Unauthorized' => $error('The credential is missing or invalid.'),
            'Forbidden' => $error('The credential lacks the ability this endpoint requires.'),
            'NotFound' => $error('No resource with that id is visible to the request.'),
            'UnprocessableEntity' => $error('A query parameter, header or body field is invalid. Each error names its source.'),
            'TooManyRequests' => $error('The rate limit was exceeded. Retry after the Retry-After header.'),
        ];
    }
}
