<?php

namespace Lunar\Api\Query;

use Illuminate\Http\Request;
use Lunar\Api\Http\Exceptions\InvalidQueryException;
use Lunar\Api\Registry\ResourceDefinition;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;

/**
 * Parses the JSON:API-style grammar (`include`, `fields[type]`,
 * `filter[name][op]`, `sort`, `page[number|size|cursor]`) against a resource
 * definition. Anything unregistered is rejected with a 422 that lists what is
 * allowed, so a consumer never silently gets an unfiltered result.
 */
final class QueryParser
{
    /** @var array<int, array<string, mixed>> */
    private array $errors = [];

    public function __construct(private readonly int $maxIncludeDepth = 3) {}

    /**
     * @param  bool  $collection  whether filters, sorts and pagination apply (index) or are ignored (show)
     */
    public function parse(Request $request, ResourceDefinition $definition, SerializationContext $context, bool $collection = true): Query
    {
        $this->errors = [];

        $includes = $this->parseIncludes($request->query('include'), $definition, $context);
        $fields = $this->parseFields($request->query('fields'), $context);

        $filters = $collection ? $this->parseFilters($request->query('filter'), $definition, $context) : [];
        $sorts = $collection ? $this->parseSorts($request->query('sort'), $definition, $context) : [];
        [$pageNumber, $pageSize, $cursor] = $collection
            ? $this->parsePage($request->query('page'), $definition)
            : [null, $definition->resource->defaultPageSize(), null];

        if ($this->errors !== []) {
            throw new InvalidQueryException($this->errors);
        }

        return new Query($includes, $fields, $filters, $sorts, $pageNumber, $pageSize, $cursor);
    }

    private function parseIncludes(mixed $raw, ResourceDefinition $definition, SerializationContext $context): IncludeTree
    {
        if ($raw === null || $raw === '') {
            return new IncludeTree;
        }

        if (! is_string($raw)) {
            $this->error('include', 'malformed_parameter', ['parameter' => 'include']);

            return new IncludeTree;
        }

        $paths = Filter::listValue($raw);
        $valid = [];

        foreach ($paths as $path) {
            if ($this->validateIncludePath($path, $definition, $context)) {
                $valid[] = $path;
            }
        }

        return IncludeTree::fromPaths($valid);
    }

    private function validateIncludePath(string $path, ResourceDefinition $definition, SerializationContext $context): bool
    {
        $segments = explode('.', $path);

        if (count($segments) > $this->maxIncludeDepth) {
            $this->error('include', 'include_too_deep', ['value' => $path, 'max' => $this->maxIncludeDepth]);

            return false;
        }

        $current = $definition;

        foreach ($segments as $segment) {
            $include = $current->embed($segment);

            if (! $include || ! $include->visibleTo($context)) {
                $allowed = array_keys(array_filter($current->includes(), fn ($candidate) => $candidate->visibleTo($context)));

                $this->error('include', 'unknown_include', [
                    'value' => $path,
                    'type' => $current->type(),
                    'allowed' => $allowed === [] ? '-' : implode(', ', $allowed),
                ]);

                return false;
            }

            $current = $context->registry->definition($include->resource);
        }

        return true;
    }

    /** @return array<string, array<int, string>> */
    private function parseFields(mixed $raw, SerializationContext $context): array
    {
        if ($raw === null) {
            return [];
        }

        if (! is_array($raw)) {
            $this->error('fields', 'malformed_parameter', ['parameter' => 'fields']);

            return [];
        }

        $fields = [];

        foreach ($raw as $type => $list) {
            if (! $context->registry->has((string) $type)) {
                $this->error("fields[{$type}]", 'unknown_type', [
                    'value' => $type,
                    'allowed' => implode(', ', $context->registry->types()),
                ]);

                continue;
            }

            $definition = $context->registry->definition((string) $type);
            $selectable = $definition->selectableNames($context);
            $names = is_array($list) ? array_values($list) : Filter::listValue($list);

            foreach ($names as $name) {
                if (! in_array($name, $selectable, true)) {
                    $this->error("fields[{$type}]", 'unknown_field', [
                        'value' => $name,
                        'type' => $type,
                        'allowed' => implode(', ', $selectable),
                    ]);
                }
            }

            $fields[(string) $type] = $names;
        }

        return $fields;
    }

    /** @return array<int, array{filter: Filter, operator: string, value: mixed}> */
    private function parseFilters(mixed $raw, ResourceDefinition $definition, SerializationContext $context): array
    {
        if ($raw === null) {
            return [];
        }

        if (! is_array($raw)) {
            $this->error('filter', 'malformed_parameter', ['parameter' => 'filter']);

            return [];
        }

        $visible = array_filter($definition->filters(), fn (Filter $filter) => $filter->visibleTo($context));
        $filters = [];

        foreach ($raw as $name => $value) {
            $filter = $visible[$name] ?? null;

            if (! $filter) {
                $this->error("filter[{$name}]", 'unknown_filter', [
                    'value' => $name,
                    'allowed' => $visible === [] ? '-' : implode(', ', array_keys($visible)),
                ]);

                continue;
            }

            // filter[name]=v, filter[name][]=a&filter[name][]=b, or filter[name][op]=v.
            $pairs = match (true) {
                ! is_array($value) => [['eq', $value]],
                array_is_list($value) => [['in', $value]],
                default => array_map(fn ($operator, $operand) => [(string) $operator, $operand], array_keys($value), $value),
            };

            foreach ($pairs as [$operator, $operand]) {
                if (! $filter->allows($operator)) {
                    $this->error("filter[{$name}][{$operator}]", 'unknown_operator', [
                        'value' => $operator,
                        'filter' => $name,
                        'allowed' => implode(', ', $filter->allowedOperators()),
                    ]);

                    continue;
                }

                $filters[] = ['filter' => $filter, 'operator' => $operator, 'value' => $operand];
            }
        }

        return $filters;
    }

    /** @return array<int, array{sort: Sort, direction: string}> */
    private function parseSorts(mixed $raw, ResourceDefinition $definition, SerializationContext $context): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (! is_string($raw)) {
            $this->error('sort', 'malformed_parameter', ['parameter' => 'sort']);

            return [];
        }

        $visible = array_filter($definition->sorts(), fn ($sort) => $sort->visibleTo($context));
        $sorts = [];

        foreach (Filter::listValue($raw) as $key) {
            $direction = str_starts_with($key, '-') ? 'desc' : 'asc';
            $name = ltrim($key, '-');
            $sort = $visible[$name] ?? null;

            if (! $sort) {
                $this->error('sort', 'unknown_sort', [
                    'value' => $name,
                    'allowed' => $visible === [] ? '-' : implode(', ', array_keys($visible)),
                ]);

                continue;
            }

            $sorts[] = ['sort' => $sort, 'direction' => $direction];
        }

        return $sorts;
    }

    /** @return array{0: int|null, 1: int, 2: string|null} */
    private function parsePage(mixed $raw, ResourceDefinition $definition): array
    {
        $resource = $definition->resource;
        $size = $resource->defaultPageSize();

        if ($raw === null) {
            return [null, $size, null];
        }

        if (! is_array($raw)) {
            $this->error('page', 'malformed_parameter', ['parameter' => 'page']);

            return [null, $size, null];
        }

        $number = null;
        $cursor = null;

        if (array_key_exists('size', $raw)) {
            if (! ctype_digit((string) $raw['size']) || (int) $raw['size'] < 1 || (int) $raw['size'] > $resource->maxPageSize()) {
                $this->error('page[size]', 'invalid_page_size', ['max' => $resource->maxPageSize()]);
            } else {
                $size = (int) $raw['size'];
            }
        }

        if (array_key_exists('number', $raw)) {
            if (! ctype_digit((string) $raw['number']) || (int) $raw['number'] < 1) {
                $this->error('page[number]', 'invalid_page_number', []);
            } else {
                $number = (int) $raw['number'];
            }
        }

        if (array_key_exists('cursor', $raw)) {
            if (! $resource->supportsCursorPagination()) {
                $this->error('page[cursor]', 'cursor_unsupported', ['type' => $definition->type()]);
            } elseif ($number !== null) {
                $this->error('page[cursor]', 'cursor_and_number', []);
            } elseif (! is_string($raw['cursor']) || $raw['cursor'] === '') {
                $this->error('page[cursor]', 'invalid_cursor', []);
            } else {
                $cursor = $raw['cursor'];
            }
        }

        foreach (array_diff(array_keys($raw), ['size', 'number', 'cursor']) as $unknown) {
            $this->error("page[{$unknown}]", 'unknown_page_key', ['value' => $unknown]);
        }

        return [$number, $size, $cursor];
    }

    /** @param  array<string, mixed>  $replace */
    private function error(string $parameter, string $code, array $replace): void
    {
        $this->errors[] = [
            'code' => $code,
            'detail' => __("api::errors.query.{$code}", $replace),
            'source' => ['parameter' => $parameter],
        ];
    }
}
