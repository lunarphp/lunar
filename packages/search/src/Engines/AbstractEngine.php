<?php

namespace Lunar\Search\Engines;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Product;
use Lunar\Search\Data\Builder\SearchQuery;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Pipelines\SearchRequest;
use Lunar\Search\Pipelines\SearchResponse;

abstract class AbstractEngine
{
    protected string $modelType = Product::class;

    protected array $queryExtenders = [];

    protected string $query = '';

    protected array $filters = [];

    protected array $facets = [];

    protected int $perPage = 50;

    /** Null until page() is called: the page then resolves from the request, as Scout does. */
    protected ?int $page = null;

    /** Extra engine request parameters, merged into the engine request last. */
    protected array $params = [];

    protected string $sort = '';

    protected string $sortRaw = '';

    public function getModelType(): string
    {
        return $this->modelType;
    }

    public function extendQuery(\Closure $callable): self
    {
        $this->queryExtenders[] = $callable;

        return $this;
    }

    public function filter(array $filters): self
    {
        foreach ($filters as $key => $value) {
            $this->addFilter($key, $value);
        }

        return $this;
    }

    public function addFilter($key, $value): self
    {
        $this->filters[$key] = $value;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function page(int $page): static
    {
        $this->page = max(1, $page);

        return $this;
    }

    public function getPage(): int
    {
        return $this->page ?? Paginator::resolveCurrentPage();
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Merge engine-specific request parameters, applied after everything the
     * engine builds itself. A null value removes the parameter from the
     * request. Used by request-pipeline stages to change retrieval without
     * engine-specific code living in the engine.
     */
    public function withParams(array $params): static
    {
        $this->params = [...$this->params, ...$params];

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getFacets(): array
    {
        return $this->facets;
    }

    public function setFacets(array $facets): self
    {
        $this->facets = $facets;

        return $this;
    }

    public function removeFacet(string $field, mixed $value = null): self
    {
        if (empty($this->facets[$field])) {
            return $this;
        }

        if (! $value) {
            unset($this->facets[$field]);

            return $this;
        }

        $this->facets[$field] = collect($this->facets[$field])->reject(
            fn ($faceValue) => $faceValue == $value
        )->toArray();

        return $this;
    }

    public function sort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function sortRaw(string $sort): self
    {
        $this->sortRaw = $sort;

        return $this;
    }

    public function query(string $query): AbstractEngine
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    protected function getRawResults(\Closure $builder): LengthAwarePaginator
    {
        return $this->modelType::search($this->query, $builder)->paginateRaw(perPage: $this->perPage, page: $this->page);
    }

    /**
     * Run the request pipeline. Engines call this at the top of get() so
     * stages can adjust the request before the engine queries.
     */
    protected function pipeRequest(): SearchRequest
    {
        $request = new SearchRequest($this, $this->getPage(), $this->perPage);

        return app(Pipeline::class)
            ->send($request)
            ->through(config('lunar.search.pipelines.request', []))
            ->thenReturn();
    }

    /**
     * Run the results pipeline. Engines wrap their return value in this so
     * stages can reorder, annotate or replace the built results.
     */
    protected function pipeResults(SearchRequest $request, SearchResults $results): SearchResults
    {
        $response = app(Pipeline::class)
            ->send(new SearchResponse($request, $results))
            ->through(config('lunar.search.pipelines.results', []))
            ->thenReturn();

        return $response->results;
    }

    /**
     * Apply withParams() overrides to a built request array. Null removes
     * the key so a stage can drop a parameter the engine would otherwise send.
     */
    protected function applyParamOverrides(array $params): array
    {
        foreach ($this->params as $key => $value) {
            if ($value === null) {
                unset($params[$key]);

                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    protected function getFacetConfig(?string $field = null): ?array
    {
        if (! $field) {
            return config('lunar.search.facets.'.$this->modelType, []);
        }

        return config('lunar.search.facets.'.$this->modelType, [])[$field] ?? [];
    }

    public function getSearchQueries(): Collection
    {
        $facets = $this->getFacetConfig();

        $queries = [
            SearchQuery::from([
                'query' => $this->query,
                'facets' => array_keys($facets),
                'facet_filters' => $this->facets,
            ]),
        ];

        foreach ($this->facets as $facetField => $facetFilterValues) {
            $queries[] = SearchQuery::from([
                'query' => $this->query,
                'facets' => [$facetField],
                'facet_filters' => collect($this->facets)->reject(
                    fn ($value, $field) => $field === $facetField
                )->toArray(),
            ]);
        }

        foreach ($this->queryExtenders as $extender) {
            $params = call_user_func($extender, $this, $queries);
        }

        return collect($queries);
    }

    /**
     * The requested sort split into [field, direction] for the results payload,
     * or [null, null] when no sort was requested. An invalid or missing
     * direction falls back to `asc`.
     *
     * @return array{0: string|null, 1: string|null}
     */
    protected function getSortParts(): array
    {
        if (! $this->sort) {
            return [null, null];
        }

        [$field, $direction] = array_pad(explode(':', $this->sort, 2), 2, null);

        return [$field ?: null, in_array($direction, ['asc', 'desc']) ? $direction : 'asc'];
    }

    protected function sortByIsValid(): bool
    {
        $sort = $this->sort;

        if (! $sort) {
            return true;
        }

        $parts = explode(':', $sort);

        if (! isset($parts[1])) {
            return false;
        }

        if (! in_array($parts[1], ['asc', 'desc'])) {
            return false;
        }

        $config = $this->getFieldConfig();

        if (empty($config)) {
            return false;
        }

        $field = collect($config)->first(
            fn ($field) => $field['name'] == $parts[0]
        );

        return $field && ($field['sort'] ?? false);
    }

    public function deleteByIds(Collection $ids): array
    {
        return [];
    }

    abstract public function get(): mixed;

    abstract protected function getFieldConfig(): array;
}
