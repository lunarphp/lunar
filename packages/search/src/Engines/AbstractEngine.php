<?php

namespace Lunar\Search\Engines;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Lunar\Models\Product;
use Lunar\Search\Data\Builder\SearchQuery;

abstract class AbstractEngine
{
    protected string $modelType = Product::class;

    protected array $queryExtenders = [];

    protected string $query = '';

    protected array $filters = [];

    protected array $facets = [];

    protected int $perPage = 50;

    protected string $sort = '';

    protected string $sortRaw = '';

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
        return $this->modelType::search($this->query, $builder)->paginateRaw(perPage: $this->perPage);
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
