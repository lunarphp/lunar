<?php

namespace Lunar\Search\Pipelines;

use Lunar\Search\Engines\AbstractEngine;

/**
 * Passable for the `lunar.search.pipelines.request` pipeline. Stages run
 * before the engine queries and may change the page, page size, sort,
 * filters or request parameters on the engine. `context` is a free-form bag
 * for stages to hand state to the results pipeline.
 */
final class SearchRequest
{
    /** @param array<string, mixed> $context */
    public function __construct(
        public AbstractEngine $engine,
        public int $requestedPage,
        public int $requestedPerPage,
        public array $context = [],
    ) {}
}
