<?php

namespace Lunar\Api\Query;

use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Sort;

/**
 * A validated request query: every filter, sort and include named here exists
 * on the resource and is visible to the caller.
 */
final class Query
{
    /**
     * @param  array<string, array<int, string>>  $fields  type => field names
     * @param  array<int, array{filter: Filter, operator: string, value: mixed}>  $filters
     * @param  array<int, array{sort: Sort, direction: string}>  $sorts
     */
    public function __construct(
        public readonly IncludeTree $includes = new IncludeTree,
        public readonly array $fields = [],
        public readonly array $filters = [],
        public readonly array $sorts = [],
        public readonly ?int $pageNumber = null,
        public readonly int $pageSize = 15,
        public readonly ?string $cursor = null,
    ) {}
}
