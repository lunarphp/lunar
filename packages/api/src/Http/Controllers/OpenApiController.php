<?php

namespace Lunar\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\OpenApi\Generator;

/**
 * `GET /{surface}/{version}/openapi.json`: the full surface document,
 * whatever the caller may see, because code generation needs every field.
 * Ability gates are marked with `x-lunar-requires`.
 */
class OpenApiController
{
    public function __construct(
        protected ApiManager $api,
        protected Generator $generator,
    ) {}

    public function __invoke(string $surface, string $version): JsonResponse
    {
        $document = $this->generator->generate($this->api->surface($surface, $version));

        return new JsonResponse($document->toArray(), 200, [], JSON_UNESCAPED_SLASHES);
    }
}
