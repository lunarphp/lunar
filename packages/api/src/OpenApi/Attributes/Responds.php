<?php

namespace Lunar\Api\OpenApi\Attributes;

use Attribute;
use InvalidArgumentException;
use Lunar\Api\Contracts\DescribesSchema;
use Lunar\Api\Resources\Resource;

/**
 * The success response of an endpoint: a resource in an item envelope, a
 * list of them (`many`, paginated by default), a bespoke schema, or nothing
 * (`status: 204`).
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Responds
{
    /**
     * @param  class-string<resource>|null  $resource
     * @param  class-string<DescribesSchema>|null  $schema  a bespoke response body, used instead of the resource
     */
    public function __construct(
        public ?string $resource = null,
        public bool $many = false,
        public ?bool $paginated = null,
        public int $status = 200,
        public ?string $schema = null,
        public ?string $description = null,
    ) {
        if ($resource !== null && ! is_subclass_of($resource, Resource::class)) {
            throw new InvalidArgumentException("[{$resource}] is not a ".Resource::class.'.');
        }

        if ($schema !== null && ! is_subclass_of($schema, DescribesSchema::class)) {
            throw new InvalidArgumentException("[{$schema}] does not implement ".DescribesSchema::class.'.');
        }
    }

    public function isPaginated(): bool
    {
        return $this->paginated ?? $this->many;
    }

    public function isEmpty(): bool
    {
        return $this->resource === null && $this->schema === null;
    }
}
