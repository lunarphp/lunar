<?php

namespace Lunar\Api\OpenApi\Attributes;

use Attribute;

/**
 * The summary and description of an endpoint in the OpenAPI document. Put it
 * on the controller method the route points at.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Operation
{
    public function __construct(
        public string $summary,
        public ?string $description = null,
    ) {}
}
